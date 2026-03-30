<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PokemonSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('pokemon')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $now = now();
        $sprite = fn(int $no) => "https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/{$no}.png";

        DB::table('pokemon')->insert([
            ['id'=>1,  'pokedex_number'=>6,   'name_ja'=>'リザードン', 'name_en'=>'charizard',  'form_name'=>null,'base_hp'=>78,  'base_attack'=>84,  'base_defense'=>78,  'base_sp_attack'=>109,'base_sp_defense'=>85, 'base_speed'=>100,'sprite_url'=>$sprite(6),   'created_at'=>$now,'updated_at'=>$now],
            ['id'=>2,  'pokedex_number'=>9,   'name_ja'=>'カメックス', 'name_en'=>'blastoise',  'form_name'=>null,'base_hp'=>79,  'base_attack'=>83,  'base_defense'=>100, 'base_sp_attack'=>85, 'base_sp_defense'=>105,'base_speed'=>78, 'sprite_url'=>$sprite(9),   'created_at'=>$now,'updated_at'=>$now],
            ['id'=>3,  'pokedex_number'=>3,   'name_ja'=>'フシギバナ', 'name_en'=>'venusaur',   'form_name'=>null,'base_hp'=>80,  'base_attack'=>82,  'base_defense'=>83,  'base_sp_attack'=>100,'base_sp_defense'=>100,'base_speed'=>80, 'sprite_url'=>$sprite(3),   'created_at'=>$now,'updated_at'=>$now],
            ['id'=>4,  'pokedex_number'=>25,  'name_ja'=>'ピカチュウ', 'name_en'=>'pikachu',    'form_name'=>null,'base_hp'=>35,  'base_attack'=>55,  'base_defense'=>40,  'base_sp_attack'=>50, 'base_sp_defense'=>50, 'base_speed'=>90, 'sprite_url'=>$sprite(25),  'created_at'=>$now,'updated_at'=>$now],
            ['id'=>5,  'pokedex_number'=>150, 'name_ja'=>'ミュウツー', 'name_en'=>'mewtwo',     'form_name'=>null,'base_hp'=>106, 'base_attack'=>110, 'base_defense'=>90,  'base_sp_attack'=>154,'base_sp_defense'=>90, 'base_speed'=>130,'sprite_url'=>$sprite(150),  'created_at'=>$now,'updated_at'=>$now],
            ['id'=>6,  'pokedex_number'=>143, 'name_ja'=>'カビゴン',   'name_en'=>'snorlax',    'form_name'=>null,'base_hp'=>160, 'base_attack'=>110, 'base_defense'=>65,  'base_sp_attack'=>65, 'base_sp_defense'=>110,'base_speed'=>30, 'sprite_url'=>$sprite(143),  'created_at'=>$now,'updated_at'=>$now],
            ['id'=>7,  'pokedex_number'=>149, 'name_ja'=>'カイリュー', 'name_en'=>'dragonite',  'form_name'=>null,'base_hp'=>91,  'base_attack'=>134, 'base_defense'=>95,  'base_sp_attack'=>100,'base_sp_defense'=>100,'base_speed'=>80, 'sprite_url'=>$sprite(149),  'created_at'=>$now,'updated_at'=>$now],
            ['id'=>8,  'pokedex_number'=>248, 'name_ja'=>'バンギラス', 'name_en'=>'tyranitar',  'form_name'=>null,'base_hp'=>100, 'base_attack'=>134, 'base_defense'=>110, 'base_sp_attack'=>95, 'base_sp_defense'=>100,'base_speed'=>61, 'sprite_url'=>$sprite(248),  'created_at'=>$now,'updated_at'=>$now],
            ['id'=>9,  'pokedex_number'=>376, 'name_ja'=>'メタグロス', 'name_en'=>'metagross',  'form_name'=>null,'base_hp'=>80,  'base_attack'=>135, 'base_defense'=>130, 'base_sp_attack'=>95, 'base_sp_defense'=>90, 'base_speed'=>70, 'sprite_url'=>$sprite(376),  'created_at'=>$now,'updated_at'=>$now],
            ['id'=>10, 'pokedex_number'=>445, 'name_ja'=>'ガブリアス', 'name_en'=>'garchomp',   'form_name'=>null,'base_hp'=>108, 'base_attack'=>130, 'base_defense'=>95,  'base_sp_attack'=>80, 'base_sp_defense'=>85, 'base_speed'=>102,'sprite_url'=>$sprite(445),  'created_at'=>$now,'updated_at'=>$now],
            ['id'=>11, 'pokedex_number'=>644, 'name_ja'=>'ゼクロム',   'name_en'=>'zekrom',     'form_name'=>null,'base_hp'=>100, 'base_attack'=>150, 'base_defense'=>120, 'base_sp_attack'=>120,'base_sp_defense'=>100,'base_speed'=>90, 'sprite_url'=>$sprite(644),  'created_at'=>$now,'updated_at'=>$now],
            ['id'=>12, 'pokedex_number'=>643, 'name_ja'=>'レシラム',   'name_en'=>'reshiram',   'form_name'=>null,'base_hp'=>100, 'base_attack'=>120, 'base_defense'=>100, 'base_sp_attack'=>150,'base_sp_defense'=>120,'base_speed'=>90, 'sprite_url'=>$sprite(643),  'created_at'=>$now,'updated_at'=>$now],
            ['id'=>13, 'pokedex_number'=>131, 'name_ja'=>'ラプラス',   'name_en'=>'lapras',     'form_name'=>null,'base_hp'=>130, 'base_attack'=>85,  'base_defense'=>80,  'base_sp_attack'=>85, 'base_sp_defense'=>95, 'base_speed'=>60, 'sprite_url'=>$sprite(131),  'created_at'=>$now,'updated_at'=>$now],
            ['id'=>14, 'pokedex_number'=>197, 'name_ja'=>'ブラッキー', 'name_en'=>'umbreon',    'form_name'=>null,'base_hp'=>95,  'base_attack'=>65,  'base_defense'=>110, 'base_sp_attack'=>60, 'base_sp_defense'=>130,'base_speed'=>65, 'sprite_url'=>$sprite(197),  'created_at'=>$now,'updated_at'=>$now],
            ['id'=>15, 'pokedex_number'=>196, 'name_ja'=>'エーフィ',   'name_en'=>'espeon',     'form_name'=>null,'base_hp'=>65,  'base_attack'=>65,  'base_defense'=>60,  'base_sp_attack'=>130,'base_sp_defense'=>95, 'base_speed'=>110,'sprite_url'=>$sprite(196),  'created_at'=>$now,'updated_at'=>$now],
            ['id'=>16, 'pokedex_number'=>373, 'name_ja'=>'ボーマンダ', 'name_en'=>'salamence',  'form_name'=>null,'base_hp'=>95,  'base_attack'=>135, 'base_defense'=>80,  'base_sp_attack'=>110,'base_sp_defense'=>80, 'base_speed'=>100,'sprite_url'=>$sprite(373),  'created_at'=>$now,'updated_at'=>$now],
            ['id'=>17, 'pokedex_number'=>381, 'name_ja'=>'ラティオス', 'name_en'=>'latios',     'form_name'=>null,'base_hp'=>80,  'base_attack'=>90,  'base_defense'=>80,  'base_sp_attack'=>130,'base_sp_defense'=>110,'base_speed'=>110,'sprite_url'=>$sprite(381),  'created_at'=>$now,'updated_at'=>$now],
            ['id'=>18, 'pokedex_number'=>380, 'name_ja'=>'ラティアス', 'name_en'=>'latias',     'form_name'=>null,'base_hp'=>80,  'base_attack'=>80,  'base_defense'=>90,  'base_sp_attack'=>110,'base_sp_defense'=>130,'base_speed'=>110,'sprite_url'=>$sprite(380),  'created_at'=>$now,'updated_at'=>$now],
            ['id'=>19, 'pokedex_number'=>384, 'name_ja'=>'レックウザ', 'name_en'=>'rayquaza',   'form_name'=>null,'base_hp'=>105, 'base_attack'=>150, 'base_defense'=>90,  'base_sp_attack'=>150,'base_sp_defense'=>90, 'base_speed'=>95, 'sprite_url'=>$sprite(384),  'created_at'=>$now,'updated_at'=>$now],
            ['id'=>20, 'pokedex_number'=>282, 'name_ja'=>'サーナイト', 'name_en'=>'gardevoir',  'form_name'=>null,'base_hp'=>68,  'base_attack'=>65,  'base_defense'=>65,  'base_sp_attack'=>125,'base_sp_defense'=>115,'base_speed'=>80, 'sprite_url'=>$sprite(282),  'created_at'=>$now,'updated_at'=>$now],
        ]);
    }
}
