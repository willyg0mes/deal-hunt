<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\GameTranslation;

class Game extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'title',
        'image',
        'store',
        'store_name',
        'normal_price',
        'sale_price',
        'discount_percent',
        'deal_ends_at',
        'description',
        'external_id',
        'release_date',
        'developer',
        'metacritic',
        'deal_url',
        'resolved_url',
        'is_active',
        'is_free_permanently',
        'free_since',
        'free_description'
    ];

    protected $casts = [
        'deal_ends_at' => 'datetime',
        'release_date' => 'date',
        'normal_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'discount_percent' => 'integer',
        'metacritic' => 'integer',
        'is_active' => 'boolean',
        'is_free_permanently' => 'boolean',
        'free_since' => 'datetime',
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'game_category');
    }

    public function wishlistedBy()
    {
        return $this->belongsToMany(User::class, 'wishlist');
    }

    public function translations()
    {
        return $this->hasMany(\App\Models\GameTranslation::class);
    }

    // Método helper para obter tradução de um campo específico
    public function getTranslation($fieldName)
    {
        if ($this->relationLoaded('translations')) {
            return $this->translations->firstWhere('field_name', $fieldName);
        }

        return $this->translations()->where('field_name', $fieldName)->first();
    }

    // Método helper para obter descrição traduzida (considera RAWG se tradução existir)
    public function getTranslatedDescription()
    {
        $translation = $this->getTranslation('description');
        return $translation ? $translation->translated_text : null;
    }

    // Método para obter a melhor descrição disponível (traduzida > original > RAWG)
    public function getBestDescription($rawgData = null)
    {
        // 1. Prioridade: descrição traduzida
        $translatedDescription = $this->getTranslatedDescription();
        if ($translatedDescription) {
            return $translatedDescription;
        }

        // 2. Se há dados do RAWG, usar description_raw
        if ($rawgData && isset($rawgData['description_raw']) && !empty($rawgData['description_raw'])) {
            return strip_tags($rawgData['description_raw']);
        }

        // 3. Fallback: descrição do banco (se não for texto de oferta)
        if ($this->description && !str_contains($this->description, 'Oferta encontrada')) {
            return $this->description;
        }

        return null;
    }

    /**
     * Scope para deals ativos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para deals que ainda não expiraram
     */
    public function scopeNotExpired($query)
    {
        return $query->where(function($q) {
            $q->where('deal_ends_at', '>', now())
              ->orWhereNull('deal_ends_at');
        });
    }

    /**
     * Scope para deals válidos (ativos e não expirados)
     */
    public function scopeValid($query)
    {
        return $query->active()->notExpired();
    }

    /**
     * Scope para jogos gratuitos permanentes
     */
    public function scopeFree($query)
    {
        return $query->where('is_free_permanently', true);
    }

    /**
     * Scope para jogos gratuitos ativos
     */
    public function scopeActiveFree($query)
    {
        return $query->free()->active();
    }

    /**
     * Mapeia IDs numéricos das lojas para nomes legíveis
     */
    public static function getStoreMapping()
    {
        return [
            '1' => 'Steam',
            '2' => 'GamersGate', 
            '3' => 'GreenManGaming',
            '7' => 'GOG',
            '11' => 'Humble Store',
            '13' => 'Uplay',
            '15' => 'Fanatical',
            '21' => 'WinGameStore',
            '23' => 'GameBillet',
            '24' => 'Voidu',
            '25' => 'Epic Games',
            '27' => 'IndieGameStand',
            '28' => 'GamesPlanet',
            '29' => 'Gamesload',
            '30' => 'GamesPlanet UK',
            '34' => 'Chrono.gg',
        ];
    }

    /**
     * Retorna o nome normalizado da loja
     */
    public function getStoreNameAttribute()
    {
        // Se já tem o nome normalizado no banco, usa ele
        if ($this->attributes['store_name']) {
            return $this->attributes['store_name'];
        }
        
        $mapping = self::getStoreMapping();
        
        // Se é um número, mapeia para o nome
        if (is_numeric($this->store)) {
            return $mapping[$this->store] ?? 'Loja Digital';
        }
        
        // Se já é um nome, normaliza
        $storeName = trim($this->store);
        
        // Mapear nomes conhecidos
        $nameMapping = [
            'Loja Desconhecida' => 'Loja Digital',
            'Epic Games' => 'Epic Games',
            'GOG' => 'GOG',
            'Steam' => 'Steam',
            'Uplay' => 'Ubisoft Connect',
            'GreenManGaming' => 'Green Man Gaming',
        ];
        
        return $nameMapping[$storeName] ?? $storeName;
    }

    /**
     * Retorna a cor do badge da loja
     */
    public function getStoreColorAttribute()
    {
        $colors = [
            'Steam' => 'bg-blue-600',
            'Epic Games' => 'bg-gray-800',
            'GOG' => 'bg-purple-600',
            'Ubisoft Connect' => 'bg-blue-500',
            'Green Man Gaming' => 'bg-green-600',
            'Humble Store' => 'bg-red-600',
            'Fanatical' => 'bg-orange-500',
            'GamersGate' => 'bg-indigo-600',
        ];
        
        return $colors[$this->store_name] ?? 'bg-gray-700';
    }

    /**
     * Capa otimizada no formato Steam header (460×215).
     * Promove capsules ruins e alinha crops RAWG ao card.
     */
    public function getOptimizedImageAttribute(): string
    {
        if (empty($this->image)) {
            return $this->placeholderCoverUrl();
        }

        $imageUrl = trim((string) $this->image);

        // CheapShark / Steam CDN: capsule minúscula → header oficial 460×215
        if ($steamHeader = $this->promoteSteamCoverUrl($imageUrl)) {
            return $steamHeader;
        }

        // RAWG: crop no mesmo aspect do card
        if (str_contains($imageUrl, 'media.rawg.io')) {
            $imageUrl = preg_replace('#/crop/\d+/\d+/#', '/crop/460/215/', $imageUrl) ?? $imageUrl;
            $imageUrl = str_replace('/scale-to-fit-down/', '/scale-to-fit/', $imageUrl);
            $imageUrl = str_replace('/thumb/', '/', $imageUrl);
        }

        return $imageUrl;
    }

    public function placeholderCoverUrl(): string
    {
        return 'https://placehold.co/460x215/1a1a1a/ffffff?text='.urlencode((string) $this->title);
    }

    /**
     * Converte URLs Steam (capsule/tiny) no header padrão da loja.
     */
    protected function promoteSteamCoverUrl(string $url): ?string
    {
        $hosts = [
            'steamcdn-a.akamaihd.net',
            'steamstatic.com',
            'shared.akamai.steamstatic.com',
            'cdn.cloudflare.steamstatic.com',
            'cdn.akamai.steamstatic.com',
            'cheapshark.com',
        ];

        $isSteamish = false;
        foreach ($hosts as $host) {
            if (str_contains($url, $host)) {
                $isSteamish = true;
                break;
            }
        }

        if (! $isSteamish && ! str_contains($url, '/steam/apps/')) {
            return null;
        }

        if (preg_match('#/(?:steam/)?apps/(\d+)/#i', $url, $matches)
            || preg_match('#apps/(\d+)#i', $url, $matches)) {
            return self::steamHeaderUrl((int) $matches[1]);
        }

        return null;
    }

    public static function steamHeaderUrl(int $appId): string
    {
        return 'https://cdn.cloudflare.steamstatic.com/steam/apps/'.$appId.'/header.jpg';
    }

    /**
     * Gera um link direto para a loja baseado no título e loja
     */
    public function getDirectStoreUrlAttribute()
    {
        $storeName = $this->store_name;
        $gameTitle = urlencode($this->title);
        
        // URLs diretas para pesquisa em cada loja
        $storeUrls = [
            'Steam' => "https://store.steampowered.com/search/?term={$gameTitle}",
            'Epic Games' => "https://store.epicgames.com/pt-BR/browse?q={$gameTitle}",
            'GOG' => "https://www.gog.com/games?search={$gameTitle}",
            'Ubisoft Connect' => "https://store.ubisoft.com/br/search/?q={$gameTitle}",
            'Green Man Gaming' => "https://www.greenmangaming.com/search/{$gameTitle}/",
            'Humble Store' => "https://www.humblebundle.com/store/search?search={$gameTitle}",
            'Fanatical' => "https://www.fanatical.com/en/search?search={$gameTitle}",
            'GamersGate' => "https://www.gamersgate.com/games?q={$gameTitle}",
            'Loja Digital' => "https://www.google.com/search?q=comprar+jogo+{$gameTitle}",
            'Chrono.gg' => "https://chrono.gg/search?query={$gameTitle}",
            'GameBillet' => "https://www.gamebillet.com/search?q={$gameTitle}",
            'GamesPlanet' => "https://gamesplanet.com/search?q={$gameTitle}",
            'GamesPlanet UK' => "https://uk.gamesplanet.com/search?q={$gameTitle}",
            'Gamesload' => "https://gamesload.com/search?q={$gameTitle}",
            'WinGameStore' => "https://www.wingamestore.com/search/?search={$gameTitle}",
        ];
        
        // Para "Loja Digital" (genérica), só usa busca no Google se não tiver deal_url
        if ($storeName === 'Loja Digital' && !$this->deal_url) {
            return "https://www.google.com/search?q=comprar+jogo+{$gameTitle}";
        }
        
        // Se a loja tem mapeamento direto, usa ele
        if (isset($storeUrls[$storeName])) {
            return $storeUrls[$storeName];
        }
        
        // Se tem deal_url, usa ele
        if ($this->deal_url) {
            return $this->deal_url;
        }
        
        // Como último recurso, usa busca no Google
        return "https://www.google.com/search?q=comprar+jogo+{$gameTitle}+{$storeName}";
    }

    /**
     * Tenta resolver o link do CheapShark para o link direto
     */
    public function getResolvedUrlAttribute()
    {
        // Se já temos uma URL resolvida salva, usa ela
        if ($this->attributes['resolved_url']) {
            return $this->attributes['resolved_url'];
        }
        
        // Se não temos deal_url, usa a URL direta da loja
        if (!$this->deal_url) {
            return $this->direct_store_url;
        }
        
        // Se não é um link do CheapShark, retorna o original
        if (!str_contains($this->deal_url, 'cheapshark.com/redirect')) {
            return $this->deal_url;
        }
        
        // Para Loja Digital com deal_url do CheapShark, usa o deal_url original
        // porque pode levar à loja real da oferta
        if ($this->store_name === 'Loja Digital') {
            return $this->deal_url;
        }
        
        // Para outras lojas com mapeamento direto, usa a URL direta
        return $this->direct_store_url;
    }

    /**
     * Retorna a melhor URL disponível para o usuário
     */
    public function getBestUrlAttribute()
    {
        // Para ofertas válidas do CheapShark, sempre priorizar o deal_url
        if ($this->deal_url && str_contains($this->deal_url, 'cheapshark.com/redirect')) {
            return $this->deal_url;
        }
        
        // Se tem uma URL resolvida específica, usa ela
        if ($this->resolved_url && !str_contains($this->resolved_url, 'search') && !str_contains($this->resolved_url, 'browse?q=')) {
            return $this->resolved_url;
        }
        
        // Se tem deal_url direto (não do CheapShark), usa ele
        if ($this->deal_url) {
            return $this->deal_url;
        }
        
        // Como último recurso, usa a URL de busca da loja
        return $this->direct_store_url;
    }
}
