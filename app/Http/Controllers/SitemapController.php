<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $base = rtrim((string) config('app.url'), '/');

        $urls = [
            ['loc' => $base.'/', 'changefreq' => 'hourly', 'priority' => '1.0'],
            ['loc' => route('games.index'), 'changefreq' => 'hourly', 'priority' => '0.9'],
            ['loc' => route('games.free'), 'changefreq' => 'daily', 'priority' => '0.8'],
        ];

        Game::query()
            ->valid()
            ->orderByDesc('updated_at')
            ->limit(5000)
            ->get(['id', 'updated_at'])
            ->each(function (Game $game) use (&$urls): void {
                $urls[] = [
                    'loc' => route('games.show', $game),
                    'lastmod' => optional($game->updated_at)->toAtomString(),
                    'changefreq' => 'daily',
                    'priority' => '0.7',
                ];
            });

        $xml = view('sitemap', compact('urls'))->render();

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }
}
