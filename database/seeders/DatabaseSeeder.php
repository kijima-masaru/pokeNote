<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AbilitySeeder::class,
            ItemSeeder::class,
            MoveSeeder::class,
            PokemonSeeder::class,
            PokemonTypeSeeder::class,
            PokemonAbilitySeeder::class,
            PokemonMoveSeeder::class,
            MegaPokemonSeeder::class,
        ]);
    }
}
