<?php

namespace Database\Factories;

use App\Models\Game;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Game>
 */
class GameFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    public function definition()
    {
        $title = $this->faker->unique()->words(3, true);
        $normalPrice = $this->faker->randomFloat(2, 20, 300);
        $discountPercent = $this->faker->numberBetween(10, 90);
        $salePrice = round($normalPrice * ((100 - $discountPercent) / 100), 2);

        return [
            'title' => ucfirst($title),
            'image' => 'https://via.placeholder.com/460x215/1a1a1a/ffffff?text=' . urlencode($title),
            'store' => $this->faker->randomElement(['Steam', 'Epic Games', 'GOG', 'Humble Store']),
            'store_name' => $this->faker->randomElement(['Steam', 'Epic Games', 'GOG', 'Humble Store']),
            'normal_price' => $normalPrice,
            'discount_percent' => $discountPercent,
            'sale_price' => $salePrice,
            'deal_ends_at' => $this->faker->dateTimeBetween('+1 day', '+30 days'),
            'description' => $this->faker->sentence(10),
            'deal_url' => 'https://example.com/game/' . Str::slug($title),
        ];
    }
}
