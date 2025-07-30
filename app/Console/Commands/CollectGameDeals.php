<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\Game;
use App\Services\RawgService;
use App\Models\Category;
use App\Notifications\GameDiscountNotification;


class CollectGameDeals extends Command
{
    protected $signature = 'collect:deals';
    protected $description = 'Coleta jogos em promoção da CheapShark API';

    public function handle()
    {
        $this->info('Coletando dados da CheapShark...');
        $page = 0;
        $total = 0;
        $processed = 0;
        $updated = 0;
        $rawg = new RawgService();
        
        // Mapeamento de storeID para nomes legíveis
        $storeMap = [
            '1' => 'Steam',
            '2' => 'GamersGate',
            '3' => 'GreenManGaming',
            '4' => 'Amazon',
            '5' => 'GameStop',
            '6' => 'Direct2Drive',
            '7' => 'GOG',
            '8' => 'Origin',
            '9' => 'Get Games',
            '10' => 'ShinyLoot',
            '11' => 'Humble Store',
            '12' => 'Desura',
            '13' => 'Uplay',
            '14' => 'IndieGameStand',
            '15' => 'Fanatical',
            '16' => 'Gamesplanet',
            '17' => 'GamesRocket',
            '18' => 'Nuuvem',
            '19' => 'Microsoft Store',
            '20' => 'GameBillet',
            '21' => 'Epic Games',
        ];

        do {
            $this->info("📥 Buscando página " . ($page + 1) . " (até 60 deals por página)...");
            
            $response = Http::timeout(30)->get('https://www.cheapshark.com/api/1.0/deals', [
                'pageNumber' => $page,
                'pageSize' => 60,
                'upperPrice' => 200,
                'onSale' => 1
            ]);

            if (!$response->successful()) {
                $this->error("Falha ao buscar página " . ($page + 1) . " da API CheapShark");
                break;
            }

            $deals = $response->json();

            if (empty($deals)) {
                $this->info("Página " . ($page + 1) . " retornou vazia. Finalizando busca.");
                break;
            }

            $this->info("✅ " . count($deals) . " deals encontrados na página " . ($page + 1) . ". Processando...");
            
            $bar = $this->output->createProgressBar(count($deals));
            $bar->start();

            foreach ($deals as $deal) {
                $bar->advance();
                
                $dealUrl = 'https://www.cheapshark.com/redirect?dealID=' . $deal['dealID'];

                $game = Game::updateOrCreate(
                    ['external_id' => $deal['dealID']],
                    [
                        'title' => $deal['title'],
                        'image' => $deal['thumb'] ?? 'https://via.placeholder.com/300x400?text=Game',
                        'store' => $deal['storeID'],
                        'store_name' => $storeMap[$deal['storeID']] ?? 'Loja Desconhecida',
                        'normal_price' => $deal['normalPrice'],
                        'sale_price' => $deal['salePrice'],
                        'discount_percent' => intval($deal['savings']),
                        'deal_ends_at' => now()->addDays(7), // 7 dias para dar tempo de ver a oferta
                        'external_id' => $deal['dealID'],
                        'deal_url' => $dealUrl,
                        'is_active' => true,
                    ]
                );

                if ($game->wasRecentlyCreated) {
                    $processed++;
                } else {
                    $updated++;
                }

                $isNewDeal = !$game->wasRecentlyCreated && $game->isDirty('sale_price');

                if ($isNewDeal) {
                    foreach ($game->wishlistedBy as $user) {
                        $user->notify(new \App\Notifications\GameDiscountNotification($game));
                    }
                }

                // 🔎 Buscar dados extras na RAWG (com cache e moderação)
                if ($game->wasRecentlyCreated) {
                    $cacheKey = 'rawg_data_' . md5($game->title);
                    $rawgData = cache()->remember($cacheKey, 3600, function() use ($rawg, $game) {
                        sleep(1); // respeita limites da API
                        return $rawg->searchGame($game->title);
                    });

                    if ($rawgData) {
                        // Atualiza imagem
                        if (!empty($rawgData['background_image'])) {
                            $game->image = $rawgData['background_image'];
                        }
                        // Salva descrição
                        if (!empty($rawgData['description_raw'])) {
                            $game->description = $rawgData['description_raw'];
                        }
                        // Salva data de lançamento
                        if (!empty($rawgData['released'])) {
                            $game->release_date = $rawgData['released'];
                        }
                        // Salva desenvolvedor
                        if (!empty($rawgData['developers'][0]['name'])) {
                            $game->developer = $rawgData['developers'][0]['name'];
                        }
                        // Salva nota/metacritic
                        if (!empty($rawgData['metacritic'])) {
                            $game->metacritic = $rawgData['metacritic'];
                        }
                        $game->save();

                        // Atualiza categorias/gêneros
                        if (!empty($rawgData['genres'])) {
                            $categoryIds = [];
                            foreach ($rawgData['genres'] as $genre) {
                                $category = Category::firstOrCreate(
                                    ['name' => $genre['name']],
                                    ['slug' => Str::slug($genre['name'])]
                                );
                                $categoryIds[] = $category->id;
                            }
                            $game->categories()->sync($categoryIds);
                        }
                    }
                }

                $total++;
            }

            $bar->finish();
            $this->newLine();
            
            $page++;
            
            // Pausa entre páginas para evitar rate limiting
            if (!empty($deals)) {
                sleep(1);
            }
            
        } while (!empty($deals));

        $this->newLine();
        $this->table(['Estatística', 'Quantidade'], [
            ['Páginas processadas', $page],
            ['Deals coletados', $total],
            ['Jogos novos', $processed],
            ['Jogos atualizados', $updated],
            ['Total de jogos', Game::count()],
        ]);

        $this->info("✅ Coleta finalizada. {$total} deals processados em {$page} páginas.");
    }
}
