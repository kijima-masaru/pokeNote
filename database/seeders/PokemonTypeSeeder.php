<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PokemonTypeSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('pokemon_types')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        DB::table('pokemon_types')->insert([
            // リザードン: ほのお/ひこう
            ['pokemon_id'=>1,  'type'=>'fire',     'slot'=>1],
            ['pokemon_id'=>1,  'type'=>'flying',   'slot'=>2],
            // カメックス: みず
            ['pokemon_id'=>2,  'type'=>'water',    'slot'=>1],
            // フシギバナ: くさ/どく
            ['pokemon_id'=>3,  'type'=>'grass',    'slot'=>1],
            ['pokemon_id'=>3,  'type'=>'poison',   'slot'=>2],
            // ピカチュウ: でんき
            ['pokemon_id'=>4,  'type'=>'electric', 'slot'=>1],
            // ミュウツー: エスパー
            ['pokemon_id'=>5,  'type'=>'psychic',  'slot'=>1],
            // カビゴン: ノーマル
            ['pokemon_id'=>6,  'type'=>'normal',   'slot'=>1],
            // カイリュー: ドラゴン/ひこう
            ['pokemon_id'=>7,  'type'=>'dragon',   'slot'=>1],
            ['pokemon_id'=>7,  'type'=>'flying',   'slot'=>2],
            // バンギラス: いわ/あく
            ['pokemon_id'=>8,  'type'=>'rock',     'slot'=>1],
            ['pokemon_id'=>8,  'type'=>'dark',     'slot'=>2],
            // メタグロス: はがね/エスパー
            ['pokemon_id'=>9,  'type'=>'steel',    'slot'=>1],
            ['pokemon_id'=>9,  'type'=>'psychic',  'slot'=>2],
            // ガブリアス: ドラゴン/じめん
            ['pokemon_id'=>10, 'type'=>'dragon',   'slot'=>1],
            ['pokemon_id'=>10, 'type'=>'ground',   'slot'=>2],
            // ゼクロム: ドラゴン/でんき
            ['pokemon_id'=>11, 'type'=>'dragon',   'slot'=>1],
            ['pokemon_id'=>11, 'type'=>'electric', 'slot'=>2],
            // レシラム: ドラゴン/ほのお
            ['pokemon_id'=>12, 'type'=>'dragon',   'slot'=>1],
            ['pokemon_id'=>12, 'type'=>'fire',     'slot'=>2],
            // ラプラス: みず/こおり
            ['pokemon_id'=>13, 'type'=>'water',    'slot'=>1],
            ['pokemon_id'=>13, 'type'=>'ice',      'slot'=>2],
            // ブラッキー: あく
            ['pokemon_id'=>14, 'type'=>'dark',     'slot'=>1],
            // エーフィ: エスパー
            ['pokemon_id'=>15, 'type'=>'psychic',  'slot'=>1],
            // ボーマンダ: ドラゴン/ひこう
            ['pokemon_id'=>16, 'type'=>'dragon',   'slot'=>1],
            ['pokemon_id'=>16, 'type'=>'flying',   'slot'=>2],
            // ラティオス: ドラゴン/エスパー
            ['pokemon_id'=>17, 'type'=>'dragon',   'slot'=>1],
            ['pokemon_id'=>17, 'type'=>'psychic',  'slot'=>2],
            // ラティアス: ドラゴン/エスパー
            ['pokemon_id'=>18, 'type'=>'dragon',   'slot'=>1],
            ['pokemon_id'=>18, 'type'=>'psychic',  'slot'=>2],
            // レックウザ: ドラゴン/ひこう
            ['pokemon_id'=>19, 'type'=>'dragon',   'slot'=>1],
            ['pokemon_id'=>19, 'type'=>'flying',   'slot'=>2],
            // サーナイト: エスパー/フェアリー
            ['pokemon_id'=>20, 'type'=>'psychic',  'slot'=>1],
            ['pokemon_id'=>20, 'type'=>'fairy',    'slot'=>2],
        ]);
    }
}
