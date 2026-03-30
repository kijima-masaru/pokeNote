<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PokemonAbilitySeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('pokemon_abilities')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        DB::table('pokemon_abilities')->insert([
            // リザードン: もうか / もらいび(夢)
            ['pokemon_id'=>1,  'ability_id'=>1,  'slot'=>1],
            ['pokemon_id'=>1,  'ability_id'=>23, 'slot'=>3],
            // カメックス: げきりゅう / あめふらし(夢)
            ['pokemon_id'=>2,  'ability_id'=>2,  'slot'=>1],
            ['pokemon_id'=>2,  'ability_id'=>8,  'slot'=>3],
            // フシギバナ: しんりょく / ようりょくそ代わりにちからずく(夢)
            ['pokemon_id'=>3,  'ability_id'=>3,  'slot'=>1],
            ['pokemon_id'=>3,  'ability_id'=>25, 'slot'=>3],
            // ピカチュウ: せいでんき
            ['pokemon_id'=>4,  'ability_id'=>24, 'slot'=>1],
            // ミュウツー: プレッシャー / てんきや(夢)
            ['pokemon_id'=>5,  'ability_id'=>19, 'slot'=>1],
            ['pokemon_id'=>5,  'ability_id'=>10, 'slot'=>3],
            // カビゴン: めんえき代わりにてんねん / くいしんぼう代わりにちからずく(夢)
            ['pokemon_id'=>6,  'ability_id'=>11, 'slot'=>1],
            ['pokemon_id'=>6,  'ability_id'=>25, 'slot'=>3],
            // カイリュー: マルチスケイル(夢)
            ['pokemon_id'=>7,  'ability_id'=>12, 'slot'=>1],
            ['pokemon_id'=>7,  'ability_id'=>17, 'slot'=>3],
            // バンギラス: すなおこし
            ['pokemon_id'=>8,  'ability_id'=>6,  'slot'=>1],
            // メタグロス: クリアボディ代わりにプレッシャー / はがねのせいしん代わりにてんねん(夢)
            ['pokemon_id'=>9,  'ability_id'=>19, 'slot'=>1],
            ['pokemon_id'=>9,  'ability_id'=>11, 'slot'=>3],
            // ガブリアス: さめはだ代わりにいかく / さめはだ(夢)
            ['pokemon_id'=>10, 'ability_id'=>12, 'slot'=>1],
            ['pokemon_id'=>10, 'ability_id'=>6,  'slot'=>3],
            // ゼクロム: テラボルテージ代わりにでんきエンジン
            ['pokemon_id'=>11, 'ability_id'=>14, 'slot'=>1],
            // レシラム: タービンブレイズ代わりにもらいび
            ['pokemon_id'=>12, 'ability_id'=>23, 'slot'=>1],
            // ラプラス: ちょすい / ゆきがくれ(夢)
            ['pokemon_id'=>13, 'ability_id'=>13, 'slot'=>1],
            ['pokemon_id'=>13, 'ability_id'=>5,  'slot'=>3],
            // ブラッキー: シンクロ / てんねん(夢)
            ['pokemon_id'=>14, 'ability_id'=>21, 'slot'=>1],
            ['pokemon_id'=>14, 'ability_id'=>11, 'slot'=>3],
            // エーフィ: シンクロ / マジックミラー代わりにトレース(夢)
            ['pokemon_id'=>15, 'ability_id'=>21, 'slot'=>1],
            ['pokemon_id'=>15, 'ability_id'=>20, 'slot'=>2],
            // ボーマンダ: いかく / マルチスケイル(夢)
            ['pokemon_id'=>16, 'ability_id'=>12, 'slot'=>1],
            ['pokemon_id'=>16, 'ability_id'=>17, 'slot'=>3],
            // ラティオス: ふゆう代わりにトレース
            ['pokemon_id'=>17, 'ability_id'=>20, 'slot'=>1],
            // ラティアス: ふゆう代わりにトレース
            ['pokemon_id'=>18, 'ability_id'=>20, 'slot'=>1],
            // レックウザ: エアロック代わりにプレッシャー
            ['pokemon_id'=>19, 'ability_id'=>19, 'slot'=>1],
            // サーナイト: シンクロ / トレース / テレパシー代わりにてんねん(夢)
            ['pokemon_id'=>20, 'ability_id'=>21, 'slot'=>1],
            ['pokemon_id'=>20, 'ability_id'=>20, 'slot'=>2],
            ['pokemon_id'=>20, 'ability_id'=>11, 'slot'=>3],
        ]);
    }
}
