<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;


class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    public function run(): void
    {
        $categories = [
            'RPG' => 'rpg',
            'Ação' => 'acao',
            'Aventura' => 'aventura',
            'Terror' => 'terror',
            'Indie' => 'indie',
            'Multiplayer' => 'multiplayer',
            'Simulação' => 'simulacao',
            'Corrida' => 'corrida',
            'Esportes' => 'esportes',
            'Estratégia' => 'estrategia'
        ];

        foreach ($categories as $name => $slug) {
            Category::firstOrCreate(['name' => $name], ['slug' => $slug]);
        }
    }
}
