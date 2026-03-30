<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MegaPokemonSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $sprite = fn(string $name) => "https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/{$name}.png";

        // メガシンカポケモンデータ（PokemonSeederのIDを参照）
        // PokemonSeeder: リザードン=1, カメックス=2, フシギバナ=3, メタグロス=9, ガブリアス=10, ボーマンダ=16, ラティオス=17, ラティアス=18
        $megaPokemon = [
            // メガリザードンX
            ['pokedex_number'=>6, 'name_ja'=>'メガリザードンX', 'name_en'=>'charizard-mega-x', 'form_name'=>'メガX',
             'base_hp'=>78,'base_attack'=>130,'base_defense'=>111,'base_sp_attack'=>130,'base_sp_defense'=>85,'base_speed'=>100,
             'sprite_url'=>$sprite('10034'),'is_mega'=>true,'base_pokemon_id'=>1],
            // メガリザードンY
            ['pokedex_number'=>6, 'name_ja'=>'メガリザードンY', 'name_en'=>'charizard-mega-y', 'form_name'=>'メガY',
             'base_hp'=>78,'base_attack'=>104,'base_defense'=>78,'base_sp_attack'=>159,'base_sp_defense'=>115,'base_speed'=>100,
             'sprite_url'=>$sprite('10035'),'is_mega'=>true,'base_pokemon_id'=>1],
            // メガフシギバナ
            ['pokedex_number'=>3, 'name_ja'=>'メガフシギバナ', 'name_en'=>'venusaur-mega', 'form_name'=>'メガ',
             'base_hp'=>80,'base_attack'=>100,'base_defense'=>123,'base_sp_attack'=>122,'base_sp_defense'=>120,'base_speed'=>80,
             'sprite_url'=>$sprite('10033'),'is_mega'=>true,'base_pokemon_id'=>3],
            // メガカメックス
            ['pokedex_number'=>9, 'name_ja'=>'メガカメックス', 'name_en'=>'blastoise-mega', 'form_name'=>'メガ',
             'base_hp'=>79,'base_attack'=>103,'base_defense'=>120,'base_sp_attack'=>135,'base_sp_defense'=>115,'base_speed'=>78,
             'sprite_url'=>$sprite('10036'),'is_mega'=>true,'base_pokemon_id'=>2],
            // メガメタグロス
            ['pokedex_number'=>376, 'name_ja'=>'メガメタグロス', 'name_en'=>'metagross-mega', 'form_name'=>'メガ',
             'base_hp'=>80,'base_attack'=>145,'base_defense'=>150,'base_sp_attack'=>105,'base_sp_defense'=>110,'base_speed'=>110,
             'sprite_url'=>$sprite('10077'),'is_mega'=>true,'base_pokemon_id'=>9],
            // メガガブリアス
            ['pokedex_number'=>445, 'name_ja'=>'メガガブリアス', 'name_en'=>'garchomp-mega', 'form_name'=>'メガ',
             'base_hp'=>108,'base_attack'=>170,'base_defense'=>115,'base_sp_attack'=>120,'base_sp_defense'=>95,'base_speed'=>92,
             'sprite_url'=>$sprite('10058'),'is_mega'=>true,'base_pokemon_id'=>10],
            // メガボーマンダ
            ['pokedex_number'=>373, 'name_ja'=>'メガボーマンダ', 'name_en'=>'salamence-mega', 'form_name'=>'メガ',
             'base_hp'=>95,'base_attack'=>145,'base_defense'=>130,'base_sp_attack'=>120,'base_sp_defense'=>90,'base_speed'=>120,
             'sprite_url'=>$sprite('10070'),'is_mega'=>true,'base_pokemon_id'=>16],
            // メガラティオス
            ['pokedex_number'=>381, 'name_ja'=>'メガラティオス', 'name_en'=>'latios-mega', 'form_name'=>'メガ',
             'base_hp'=>80,'base_attack'=>130,'base_defense'=>100,'base_sp_attack'=>160,'base_sp_defense'=>120,'base_speed'=>110,
             'sprite_url'=>$sprite('10076'),'is_mega'=>true,'base_pokemon_id'=>17],
            // メガラティアス
            ['pokedex_number'=>380, 'name_ja'=>'メガラティアス', 'name_en'=>'latias-mega', 'form_name'=>'メガ',
             'base_hp'=>80,'base_attack'=>100,'base_defense'=>120,'base_sp_attack'=>140,'base_sp_defense'=>150,'base_speed'=>110,
             'sprite_url'=>$sprite('10075'),'is_mega'=>true,'base_pokemon_id'=>18],
        ];

        foreach ($megaPokemon as &$p) {
            $p['created_at'] = $now;
            $p['updated_at'] = $now;
        }

        $ids = DB::table('pokemon')->insertGetId($megaPokemon[0]);
        DB::table('pokemon')->insert(array_slice($megaPokemon, 1));

        // メガシンカのタイプを設定
        $typeMap = [
            'charizard-mega-x'  => ['dragon', 'fire'],
            'charizard-mega-y'  => ['fire', 'flying'],
            'venusaur-mega'     => ['grass', 'poison'],
            'blastoise-mega'    => ['water'],
            'metagross-mega'    => ['steel', 'psychic'],
            'garchomp-mega'     => ['dragon', 'ground'],
            'salamence-mega'    => ['dragon', 'flying'],
            'latios-mega'       => ['dragon', 'psychic'],
            'latias-mega'       => ['dragon', 'psychic'],
        ];

        foreach ($megaPokemon as $p) {
            $dbPokemon = DB::table('pokemon')->where('name_en', $p['name_en'])->first();
            if (!$dbPokemon) continue;
            $types = $typeMap[$p['name_en']] ?? [];
            foreach ($types as $slot => $type) {
                DB::table('pokemon_types')->insert([
                    'pokemon_id' => $dbPokemon->id,
                    'type' => $type,
                    'slot' => $slot + 1,
                ]);
            }
        }
    }
}
