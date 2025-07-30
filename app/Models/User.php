<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'profile_photo_url',
        'currency',
        'locale',
        'currency_rate',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'currency_rate' => 'decimal:4',
        ];
    }
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function wishlist()
    {
        return $this->belongsToMany(Game::class, 'wishlist');
    }
    public function loginHistories()
    {
        return $this->hasMany(LoginHistory::class);
    }
    public function trustedDevices()
    {
        return $this->hasMany(TrustedDevice::class);
    }

    /**
     * Formatar preço para a moeda do usuário
     */
    public function formatPrice(float $usdPrice): string
    {
        $conversionService = app(\App\Services\CurrencyConversionService::class);
        $convertedPrice = $conversionService->convertFromUSD($usdPrice, $this->currency ?? 'BRL');
        return $conversionService->formatCurrency($convertedPrice, $this->currency ?? 'BRL', $this->locale ?? 'pt-BR');
    }

    /**
     * Converter preço para a moeda do usuário (valor numérico)
     */
    public function convertPrice(float $usdPrice): float
    {
        $conversionService = app(\App\Services\CurrencyConversionService::class);
        return $conversionService->convertFromUSD($usdPrice, $this->currency ?? 'BRL');
    }

    /**
     * Obter símbolo da moeda do usuário
     */
    public function getCurrencySymbol(): string
    {
        $conversionService = app(\App\Services\CurrencyConversionService::class);
        $info = $conversionService->getCurrencyInfo($this->currency ?? 'BRL');
        return $info['symbol'] ?? 'R$';
    }

    /**
     * Atualizar taxa de conversão do usuário
     */
    public function updateCurrencyRate(): void
    {
        $conversionService = app(\App\Services\CurrencyConversionService::class);
        $conversionService->updateUserCurrencyRate($this);
    }
}
