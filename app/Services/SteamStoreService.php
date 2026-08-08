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
     * Resolve o AppID Steam a partir da URL da capa ou busca na loja.
     */
    public function resolveAppId(Game $game): ?int
    {
        foreach ([$game->image, $game->optimized_image ?? null] as $url) {
            if (is_string($url) && preg_match('#/(?:steam/)?apps/(\d+)/#i', $url, $matches)) {
                return (int) $matches[1];
            }
        }

        return $this->searchAppIdByTitle($game->title);
    }

    public function searchAppIdByTitle(string $title): ?int
    {
        $cacheKey = 'steam:appid:'.Str::slug(mb_strtolower(trim($title)));

        return Cache::remember($cacheKey, now()->addDays(7), function () use ($title) {
            try {
                $response = Http::timeout(6)
                    ->withHeaders(['User-Agent' => CheapShark::userAgent()])
                    ->get('https://store.steampowered.com/api/storesearch/', [
                        'term' => $title,
                        'l' => 'portuguese',
                        'cc' => 'BR',
                    ]);

                if (! $response->successful()) {
                    return null;
                }

                $item = $response->json('items.0');
                $id = $item['id'] ?? null;

                return $id ? (int) $id : null;
            } catch (\Throwable $e) {
                Log::warning('SteamStoreService searchAppId: '.$e->getMessage());

                return null;
            }
        });
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
    public function getAppDetails(int $appId, string $lang = 'brazilian'): ?array
    {
        $cacheKey = "steam:appdetails:{$appId}:{$lang}";
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
                    'cc' => 'BR',
                ]);

            if (! $response->successful()) {
                Cache::put($cacheKey, false, now()->addMinutes(30));

                return null;
            }

            $payload = $response->json((string) $appId);
            if (! ($payload['success'] ?? false) || empty($payload['data'])) {
                Cache::put($cacheKey, false, now()->addHours(6));

                return null;
            }

            $data = $payload['data'];
            Cache::put($cacheKey, $data, now()->addHours(12));

            return $data;
        } catch (\Throwable $e) {
            Log::warning('SteamStoreService getAppDetails: '.$e->getMessage());
            Cache::put($cacheKey, false, now()->addMinutes(20));

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
