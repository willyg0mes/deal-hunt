<?php

namespace App\Support;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class CheapShark
{
    public static function userAgent(): string
    {
        $url = rtrim((string) config('app.url', 'https://dealhuntgaming.com.br'), '/');

        return 'DealHunt/1.0 (+'.$url.'; dealhunt@wynext.online)';
    }

    public static function http(int $timeout = 30): PendingRequest
    {
        return Http::timeout($timeout)
            ->withHeaders([
                'User-Agent' => self::userAgent(),
                'Accept' => 'application/json',
            ]);
    }

    public static function thumbUrl(?string $thumb): ?string
    {
        if (! $thumb) {
            return null;
        }

        if (str_starts_with($thumb, 'http://') || str_starts_with($thumb, 'https://')) {
            return $thumb;
        }

        return 'https://www.cheapshark.com'.$thumb;
    }
}
