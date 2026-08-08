<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class GameCatalogQuery
{
    /**
     * @return list<string>
     */
    public static function filterKeys(): array
    {
        return ['search', 'store', 'category', 'min_discount', 'max_price', 'min_price', 'sort', 'free'];
    }

    public static function apply(Builder $query, Request $request, bool $allowFreeScope = true): Builder
    {
        if ($request->filled('search')) {
            $term = trim((string) $request->input('search'));
            $query->where('title', 'like', '%'.$term.'%');
        }

        if ($request->filled('store')) {
            $query->where('store_name', $request->input('store'));
        }

        if ($request->filled('category')) {
            $category = (string) $request->input('category');
            $query->whereHas('categories', function (Builder $q) use ($category): void {
                $q->where('slug', $category)->orWhere('name', $category);
            });
        }

        if ($request->filled('min_discount')) {
            $query->where('discount_percent', '>=', (int) $request->input('min_discount'));
        }

        if ($request->filled('min_price')) {
            $query->where('sale_price', '>=', (float) $request->input('min_price'));
        }

        if ($request->filled('max_price')) {
            $query->where('sale_price', '<=', (float) $request->input('max_price'));
        }

        if ($allowFreeScope && $request->input('free') === '1') {
            $query->where(function (Builder $q): void {
                $q->where('is_free_permanently', true)
                    ->orWhere('sale_price', '<=', 0);
            });
        }

        return static::applySort($query, (string) $request->input('sort', 'discount_desc'));
    }

    public static function applySort(Builder $query, string $sort): Builder
    {
        return match ($sort) {
            'price_asc' => $query->orderBy('sale_price')->orderByDesc('discount_percent'),
            'price_desc' => $query->orderByDesc('sale_price'),
            'title_asc' => $query->orderBy('title'),
            'newest' => $query->orderByDesc('updated_at'),
            'discount_asc' => $query->orderBy('discount_percent'),
            default => $query->orderByDesc('discount_percent')->orderBy('sale_price'),
        };
    }
}
