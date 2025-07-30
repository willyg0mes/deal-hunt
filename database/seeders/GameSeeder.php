<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Game;
use App\Models\Category;

class GameSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Game::factory(30)->create()->each(function ($game) {
            $categories = Category::inRandomOrder()->take(rand(1, 3))->pluck('id');
            $game->categories()->sync($categories);
        });
    }
}
