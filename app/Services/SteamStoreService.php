<?php

namespace App\Services;

use App\Models\Game;
use App\Support\CheapShark;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SteamStoreService
{
    /**
     * Resolve o AppID Steam a partir da capa, busca limpa ou CheapShark.
     */
    public function resolveAppId(Game $game): ?int
    {
        foreach ([$game->image, $game->optimized_image ?? null] as $url) {
            if (is_string($url) && preg_match('#/(?:steam/)?apps/(\d+)/#i', $url, $matches)) {
                return (int) $matches[1];
            }
        }

        foreach ($this->titleSearchVariants($game->title) as $variant) {
            $appId = $this->searchAppIdByTitle($variant);
            if ($appId) {
                return $appId;
            }
        }

        return $this->resolveAppIdViaCheapShark($game->title);
    }

    /**
     * Variantes do título para achar a página base na Steam
     * (remove Deluxe/Gold/Ultimate Edition etc.).
     *
     * @return list<string>
     */
    public function titleSearchVariants(string $title): array
    {
        $title = trim(html_entity_decode($title));
        $variants = [$title];

        $cleaned = preg_replace(
            '/\s*[-–—:|]\s*(Deluxe|Gold|Ultimate|Complete|Definitive|Special|Elite|Premium|Standard|GOTY|Game of the Year|Anniversary|Enhanced|Remastered|Collector.?s|Digital|Super|Legendary|Edition).*$/iu',
            '',
            $title
        ) ?? $title;
        $cleaned = preg_replace(
            '/\s+(Deluxe|Gold|Ultimate|Complete|Definitive|Special|Elite|Premium|GOTY|Edition)\b.*$/iu',
            '',
            $cleaned
        ) ?? $cleaned;
        $cleaned = trim($cleaned, " \t\n\r\0\x0B-–—:");

        if ($cleaned !== '' && strcasecmp($cleaned, $title) !== 0) {
            $variants[] = $cleaned;
        }

        // Aspas comuns em brands Ubisoft etc.
        $withApostrophe = preg_replace("/\bAssassins\b/i", "Assassin's", $cleaned ?: $title) ?? $title;
        $withApostrophe = preg_replace("/\bTom Clancys\b/i", "Tom Clancy's", $withApostrophe) ?? $withApostrophe;
        $withApostrophe = preg_replace("/\bWatch Dogs\b/i", 'Watch Dogs', $withApostrophe) ?? $withApostrophe;
        if (! in_array($withApostrophe, $variants, true)) {
            $variants[] = $withApostrophe;
        }

        // Sem subtítulo após ":"
        if (str_contains($cleaned ?: $title, ':')) {
            $beforeColon = trim(explode(':', $cleaned ?: $title, 2)[0]);
            if (mb_strlen($beforeColon) >= 4) {
                $variants[] = $beforeColon;
            }
        }

        return array_values(array_unique(array_filter($variants)));
    }

    public function searchAppIdByTitle(string $title): ?int
    {
        $cacheKey = 'steam:appid:v2:'.Str::slug(mb_strtolower(trim($title)));

        return Cache::remember($cacheKey, now()->addDays(3), function () use ($title) {
            try {
                $response = Http::timeout(8)
                    ->withHeaders(['User-Agent' => CheapShark::userAgent()])
                    ->get('https://store.steampowered.com/api/storesearch/', [
                        'term' => $title,
                        'l' => 'english',
                        'cc' => 'US',
                    ]);

                if (! $response->successful()) {
                    return null;
                }

                $items = $response->json('items') ?? [];
                if ($items === []) {
                    return null;
                }

                // Preferir match mais próximo do termo buscado
                $best = $this->pickBestSearchMatch($title, $items);

                return $best ? (int) $best['id'] : null;
            } catch (\Throwable $e) {
                Log::warning('SteamStoreService searchAppId: '.$e->getMessage());

                return null;
            }
        });
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @return array<string, mixed>|null
     */
    protected function pickBestSearchMatch(string $title, array $items): ?array
    {
        $needle = Str::lower(preg_replace('/[^a-z0-9]+/i', ' ', $title) ?? $title);
        $needle = trim(preg_replace('/\s+/', ' ', $needle) ?? $needle);

        $best = null;
        $bestScore = -1;

        foreach ($items as $item) {
            $name = (string) ($item['name'] ?? '');
            $hay = Str::lower(preg_replace('/[^a-z0-9]+/i', ' ', $name) ?? $name);
            $hay = trim(preg_replace('/\s+/', ' ', $hay) ?? $hay);

            similar_text($needle, $hay, $percent);
            $score = (float) $percent;

            if (str_starts_with($hay, $needle) || str_starts_with($needle, $hay)) {
                $score += 15;
            }
            if ($hay === $needle) {
                $score += 40;
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $item;
            }
        }

        // Evitar match péssimo (ex.: título totalmente diferente)
        if ($bestScore < 35) {
            return $items[0] ?? null;
        }

        return $best;
    }

    public function resolveAppIdViaCheapShark(string $title): ?int
    {
        foreach ($this->titleSearchVariants($title) as $variant) {
            $cacheKey = 'cheapshark:steamapp:'.Str::slug(mb_strtolower($variant));

            $appId = Cache::remember($cacheKey, now()->addDays(3), function () use ($variant) {
                try {
                    $response = CheapShark::http(10)->get('https://www.cheapshark.com/api/1.0/games', [
                        'title' => $variant,
                        'limit' => 8,
                    ]);

                    if (! $response->successful()) {
                        return null;
                    }

                    foreach ($response->json() ?? [] as $row) {
                        if (! empty($row['steamAppID'])) {
                            return (int) $row['steamAppID'];
                        }
                    }

                    return null;
                } catch (\Throwable $e) {
                    return null;
                }
            });

            if ($appId) {
                return (int) $appId;
            }
        }

        return null;
    }

    /**
     * Detalhes da loja Steam (preferência PT-BR), normalizados para a view.
     *
     * @return array<string, mixed>|null
     */
    public function getCatalogDetails(Game $game): ?array
    {
        $appId = $this->resolveAppId($game);
        if (! $appId) {
            return null;
        }

        $raw = $this->getAppDetails($appId);
        if (! $raw) {
            return null;
        }

        return $this->normalize($raw, $appId);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getAppDetails(int $appId, ?string $lang = null): ?array
    {
        $langs = $lang ? [$lang] : ['brazilian', 'english'];

        foreach ($langs as $tryLang) {
            $data = $this->fetchAppDetailsForLang($appId, $tryLang);
            if ($data) {
                return $data;
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function fetchAppDetailsForLang(int $appId, string $lang): ?array
    {
        $cacheKey = "steam:appdetails:v2:{$appId}:{$lang}";
        $cached = Cache::get($cacheKey);

        if ($cached === false) {
            return null;
        }

        if (is_array($cached)) {
            return $cached;
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders(['User-Agent' => CheapShark::userAgent()])
                ->get('https://store.steampowered.com/api/appdetails', [
                    'appids' => $appId,
                    'l' => $lang,
                    'cc' => $lang === 'brazilian' ? 'BR' : 'US',
                ]);

            if (! $response->successful()) {
                Cache::put($cacheKey, false, now()->addMinutes(20));

                return null;
            }

            $payload = $response->json((string) $appId);
            if (! ($payload['success'] ?? false) || empty($payload['data'])) {
                Cache::put($cacheKey, false, now()->addHours(2));

                return null;
            }

            $data = $payload['data'];
            Cache::put($cacheKey, $data, now()->addHours(12));

            return $data;
        } catch (\Throwable $e) {
            Log::warning('SteamStoreService getAppDetails: '.$e->getMessage());
            Cache::put($cacheKey, false, now()->addMinutes(15));

            return null;
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function normalize(array $data, int $appId): array
    {
        $screenshots = [];
        foreach ($data['screenshots'] ?? [] as $shot) {
            $image = $shot['path_full'] ?? $shot['path_thumbnail'] ?? null;
            if ($image) {
                $screenshots[] = ['image' => $image];
            }
        }

        $movies = [];
        foreach ($data['movies'] ?? [] as $movie) {
            $mp4 = $movie['mp4']['max'] ?? $movie['mp4']['480'] ?? null;
            $webm = $movie['webm']['max'] ?? $movie['webm']['480'] ?? null;
            $movieId = $movie['id'] ?? null;

            if ((! $mp4 || ! $webm) && $movieId) {
                $mp4 = $mp4 ?: "https://cdn.cloudflare.steamstatic.com/steam/apps/{$movieId}/movie_max.mp4";
                $webm = $webm ?: "https://cdn.cloudflare.steamstatic.com/steam/apps/{$movieId}/movie_max.webm";
            }

            if (! $mp4 && ! $webm && ! empty($movie['hls_h264'])) {
                $mp4 = $movie['hls_h264'];
            }

            if (! $mp4 && ! $webm) {
                continue;
            }

            $movies[] = [
                'name' => $movie['name'] ?? 'Trailer',
                'poster' => $movie['thumbnail'] ?? null,
                'mp4' => $mp4,
                'webm' => $webm,
            ];
        }

        $platforms = [];
        foreach (['windows' => 'PC (Windows)', 'mac' => 'macOS', 'linux' => 'Linux'] as $key => $label) {
            if (! empty($data['platforms'][$key])) {
                $platforms[] = ['platform' => ['name' => $label]];
            }
        }

        $genres = [];
        foreach ($data['genres'] ?? [] as $genre) {
            $name = $genre['description'] ?? null;
            if ($name) {
                $genres[] = ['name' => $name];
            }
        }

        $developers = array_map(fn ($name) => ['name' => $name], $data['developers'] ?? []);
        $publishers = array_map(fn ($name) => ['name' => $name], $data['publishers'] ?? []);

        $descriptionHtml = $data['detailed_description'] ?? ($data['about_the_game'] ?? '');
        $shortHtml = $data['short_description'] ?? '';

        return [
            'source' => 'steam',
            'steam_app_id' => $appId,
            'name' => $data['name'] ?? null,
            'description_raw' => trim(html_entity_decode(strip_tags($descriptionHtml))),
            'description_html' => $descriptionHtml,
            'short_description' => trim(html_entity_decode(strip_tags($shortHtml))),
            'screenshots' => $screenshots,
            'movies' => $movies,
            'platforms' => $platforms,
            'genres' => $genres,
            'developers' => $developers,
            'publishers' => $publishers,
            'website' => $data['website'] ?? null,
            'released' => $data['release_date']['date'] ?? null,
            'metacritic' => $data['metacritic']['score'] ?? null,
            'rating' => isset($data['metacritic']['score']) ? round(((float) $data['metacritic']['score']) / 20, 1) : null,
            'esrb_rating' => isset($data['ratings']['esrb']['rating'])
                ? ['name' => $data['ratings']['esrb']['rating']]
                : null,
            'pc_requirements' => [
                'minimum' => $data['pc_requirements']['minimum'] ?? null,
                'recommended' => $data['pc_requirements']['recommended'] ?? null,
            ],
            'background_image' => $data['header_image'] ?? Game::steamHeaderUrl($appId),
            'playtime' => null,
        ];
    }
}
