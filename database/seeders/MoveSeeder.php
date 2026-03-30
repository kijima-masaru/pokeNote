<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MoveSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('moves')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $now = now();
        DB::table('moves')->insert([
            // ほのお
            ['id'=>1,  'name_ja'=>'かえんほうしゃ',   'name_en'=>'flamethrower',    'type'=>'fire',     'category'=>'special',  'power'=>90,  'accuracy'=>100,'pp'=>15, 'priority'=>0,'makes_contact'=>false,'description'=>null,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>2,  'name_ja'=>'だいもんじ',       'name_en'=>'fire blast',      'type'=>'fire',     'category'=>'special',  'power'=>110, 'accuracy'=>85, 'pp'=>5,  'priority'=>0,'makes_contact'=>false,'description'=>null,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>3,  'name_ja'=>'ニトロチャージ',   'name_en'=>'flame charge',    'type'=>'fire',     'category'=>'physical', 'power'=>50,  'accuracy'=>100,'pp'=>20, 'priority'=>0,'makes_contact'=>true, 'description'=>null,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>4,  'name_ja'=>'フレアドライブ',   'name_en'=>'flare blitz',     'type'=>'fire',     'category'=>'physical', 'power'=>120, 'accuracy'=>100,'pp'=>15, 'priority'=>0,'makes_contact'=>true, 'description'=>null,'created_at'=>$now,'updated_at'=>$now],
            // みず
            ['id'=>5,  'name_ja'=>'ハイドロポンプ',   'name_en'=>'hydro pump',      'type'=>'water',    'category'=>'special',  'power'=>110, 'accuracy'=>80, 'pp'=>5,  'priority'=>0,'makes_contact'=>false,'description'=>null,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>6,  'name_ja'=>'なみのり',         'name_en'=>'surf',            'type'=>'water',    'category'=>'special',  'power'=>90,  'accuracy'=>100,'pp'=>15, 'priority'=>0,'makes_contact'=>false,'description'=>null,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>7,  'name_ja'=>'アクアジェット',   'name_en'=>'aqua jet',        'type'=>'water',    'category'=>'physical', 'power'=>40,  'accuracy'=>100,'pp'=>20, 'priority'=>1,'makes_contact'=>true, 'description'=>null,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>8,  'name_ja'=>'りゅうのまい',     'name_en'=>'dragon dance',    'type'=>'dragon',   'category'=>'status',   'power'=>null,'accuracy'=>null,'pp'=>20,'priority'=>0,'makes_contact'=>false,'description'=>null,'created_at'=>$now,'updated_at'=>$now],
            // でんき
            ['id'=>9,  'name_ja'=>'10まんボルト',     'name_en'=>'thunderbolt',     'type'=>'electric', 'category'=>'special',  'power'=>90,  'accuracy'=>100,'pp'=>15, 'priority'=>0,'makes_contact'=>false,'description'=>null,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>10, 'name_ja'=>'かみなり',         'name_en'=>'thunder',         'type'=>'electric', 'category'=>'special',  'power'=>110, 'accuracy'=>70, 'pp'=>10, 'priority'=>0,'makes_contact'=>false,'description'=>null,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>11, 'name_ja'=>'ボルトチェンジ',   'name_en'=>'volt switch',     'type'=>'electric', 'category'=>'special',  'power'=>70,  'accuracy'=>100,'pp'=>20, 'priority'=>0,'makes_contact'=>false,'description'=>null,'created_at'=>$now,'updated_at'=>$now],
            // こおり
            ['id'=>12, 'name_ja'=>'ふぶき',           'name_en'=>'blizzard',        'type'=>'ice',      'category'=>'special',  'power'=>110, 'accuracy'=>70, 'pp'=>5,  'priority'=>0,'makes_contact'=>false,'description'=>null,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>13, 'name_ja'=>'れいとうビーム',   'name_en'=>'ice beam',        'type'=>'ice',      'category'=>'special',  'power'=>90,  'accuracy'=>100,'pp'=>10, 'priority'=>0,'makes_contact'=>false,'description'=>null,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>14, 'name_ja'=>'こおりのつぶて',   'name_en'=>'ice shard',       'type'=>'ice',      'category'=>'physical', 'power'=>40,  'accuracy'=>100,'pp'=>30, 'priority'=>1,'makes_contact'=>false,'description'=>null,'created_at'=>$now,'updated_at'=>$now],
            // エスパー
            ['id'=>15, 'name_ja'=>'サイコキネシス',   'name_en'=>'psychic',         'type'=>'psychic',  'category'=>'special',  'power'=>90,  'accuracy'=>100,'pp'=>10, 'priority'=>0,'makes_contact'=>false,'description'=>null,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>16, 'name_ja'=>'サイコショック',   'name_en'=>'psyshock',        'type'=>'psychic',  'category'=>'special',  'power'=>80,  'accuracy'=>100,'pp'=>10, 'priority'=>0,'makes_contact'=>false,'description'=>null,'created_at'=>$now,'updated_at'=>$now],
            // ゴースト
            ['id'=>17, 'name_ja'=>'シャドーボール',   'name_en'=>'shadow ball',     'type'=>'ghost',    'category'=>'special',  'power'=>80,  'accuracy'=>100,'pp'=>15, 'priority'=>0,'makes_contact'=>false,'description'=>null,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>18, 'name_ja'=>'ゴーストダイブ',   'name_en'=>'phantom force',   'type'=>'ghost',    'category'=>'physical', 'power'=>90,  'accuracy'=>100,'pp'=>10, 'priority'=>0,'makes_contact'=>true, 'description'=>null,'created_at'=>$now,'updated_at'=>$now],
            // ドラゴン
            ['id'=>19, 'name_ja'=>'りゅうせいぐん',   'name_en'=>'draco meteor',    'type'=>'dragon',   'category'=>'special',  'power'=>130, 'accuracy'=>90, 'pp'=>5,  'priority'=>0,'makes_contact'=>false,'description'=>null,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>20, 'name_ja'=>'りゅうのはどう',   'name_en'=>'dragon pulse',    'type'=>'dragon',   'category'=>'special',  'power'=>85,  'accuracy'=>100,'pp'=>10, 'priority'=>0,'makes_contact'=>false,'description'=>null,'created_at'=>$now,'updated_at'=>$now],
            // じめん
            ['id'=>21, 'name_ja'=>'じしん',           'name_en'=>'earthquake',      'type'=>'ground',   'category'=>'physical', 'power'=>100, 'accuracy'=>100,'pp'=>10, 'priority'=>0,'makes_contact'=>false,'description'=>null,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>22, 'name_ja'=>'だいちのちから',   'name_en'=>'earth power',     'type'=>'ground',   'category'=>'special',  'power'=>90,  'accuracy'=>100,'pp'=>10, 'priority'=>0,'makes_contact'=>false,'description'=>null,'created_at'=>$now,'updated_at'=>$now],
            // いわ
            ['id'=>23, 'name_ja'=>'ストーンエッジ',   'name_en'=>'stone edge',      'type'=>'rock',     'category'=>'physical', 'power'=>100, 'accuracy'=>80, 'pp'=>5,  'priority'=>0,'makes_contact'=>false,'description'=>null,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>24, 'name_ja'=>'がんせきふうじ',   'name_en'=>'rock tomb',       'type'=>'rock',     'category'=>'physical', 'power'=>60,  'accuracy'=>95, 'pp'=>15, 'priority'=>0,'makes_contact'=>false,'description'=>null,'created_at'=>$now,'updated_at'=>$now],
            // はがね
            ['id'=>25, 'name_ja'=>'アイアンヘッド',   'name_en'=>'iron head',       'type'=>'steel',    'category'=>'physical', 'power'=>80,  'accuracy'=>100,'pp'=>15, 'priority'=>0,'makes_contact'=>true, 'description'=>null,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>26, 'name_ja'=>'ラスターカノン',   'name_en'=>'flash cannon',    'type'=>'steel',    'category'=>'special',  'power'=>80,  'accuracy'=>100,'pp'=>10, 'priority'=>0,'makes_contact'=>false,'description'=>null,'created_at'=>$now,'updated_at'=>$now],
            // かくとう
            ['id'=>27, 'name_ja'=>'インファイト',     'name_en'=>'close combat',    'type'=>'fighting', 'category'=>'physical', 'power'=>120, 'accuracy'=>100,'pp'=>5,  'priority'=>0,'makes_contact'=>true, 'description'=>null,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>28, 'name_ja'=>'かわらわり',       'name_en'=>'brick break',     'type'=>'fighting', 'category'=>'physical', 'power'=>75,  'accuracy'=>100,'pp'=>15, 'priority'=>0,'makes_contact'=>true, 'description'=>null,'created_at'=>$now,'updated_at'=>$now],
            // ひこう
            ['id'=>29, 'name_ja'=>'そらをとぶ',       'name_en'=>'fly',             'type'=>'flying',   'category'=>'physical', 'power'=>90,  'accuracy'=>95, 'pp'=>15, 'priority'=>0,'makes_contact'=>true, 'description'=>null,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>30, 'name_ja'=>'つばめがえし',     'name_en'=>'aerial ace',      'type'=>'flying',   'category'=>'physical', 'power'=>60,  'accuracy'=>null,'pp'=>20,'priority'=>0,'makes_contact'=>true, 'description'=>null,'created_at'=>$now,'updated_at'=>$now],
            // あく
            ['id'=>31, 'name_ja'=>'ふいうち',         'name_en'=>'sucker punch',    'type'=>'dark',     'category'=>'physical', 'power'=>70,  'accuracy'=>100,'pp'=>5,  'priority'=>1,'makes_contact'=>true, 'description'=>null,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>32, 'name_ja'=>'つじぎり',         'name_en'=>'night slash',     'type'=>'dark',     'category'=>'physical', 'power'=>70,  'accuracy'=>100,'pp'=>15, 'priority'=>0,'makes_contact'=>true, 'description'=>null,'created_at'=>$now,'updated_at'=>$now],
            // フェアリー
            ['id'=>33, 'name_ja'=>'ムーンフォース',   'name_en'=>'moonblast',       'type'=>'fairy',    'category'=>'special',  'power'=>95,  'accuracy'=>100,'pp'=>15, 'priority'=>0,'makes_contact'=>false,'description'=>null,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>34, 'name_ja'=>'マジカルシャイン', 'name_en'=>'dazzling gleam',  'type'=>'fairy',    'category'=>'special',  'power'=>80,  'accuracy'=>100,'pp'=>10, 'priority'=>0,'makes_contact'=>false,'description'=>null,'created_at'=>$now,'updated_at'=>$now],
            // くさ
            ['id'=>35, 'name_ja'=>'ギガドレイン',     'name_en'=>'giga drain',      'type'=>'grass',    'category'=>'special',  'power'=>75,  'accuracy'=>100,'pp'=>10, 'priority'=>0,'makes_contact'=>false,'description'=>null,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>36, 'name_ja'=>'エナジーボール',   'name_en'=>'energy ball',     'type'=>'grass',    'category'=>'special',  'power'=>90,  'accuracy'=>100,'pp'=>10, 'priority'=>0,'makes_contact'=>false,'description'=>null,'created_at'=>$now,'updated_at'=>$now],
            // ノーマル
            ['id'=>37, 'name_ja'=>'ねこだまし',       'name_en'=>'fake out',        'type'=>'normal',   'category'=>'physical', 'power'=>40,  'accuracy'=>100,'pp'=>10, 'priority'=>3,'makes_contact'=>true, 'description'=>null,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>38, 'name_ja'=>'はかいこうせん',   'name_en'=>'hyper beam',      'type'=>'normal',   'category'=>'special',  'power'=>150, 'accuracy'=>90, 'pp'=>5,  'priority'=>0,'makes_contact'=>false,'description'=>null,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>39, 'name_ja'=>'からげんき',       'name_en'=>'facade',          'type'=>'normal',   'category'=>'physical', 'power'=>70,  'accuracy'=>100,'pp'=>20, 'priority'=>0,'makes_contact'=>true, 'description'=>null,'created_at'=>$now,'updated_at'=>$now],
            // 変化技
            ['id'=>40, 'name_ja'=>'みがわり',         'name_en'=>'substitute',      'type'=>'normal',   'category'=>'status',   'power'=>null,'accuracy'=>null,'pp'=>10,'priority'=>0,'makes_contact'=>false,'description'=>null,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>41, 'name_ja'=>'まもる',           'name_en'=>'protect',         'type'=>'normal',   'category'=>'status',   'power'=>null,'accuracy'=>null,'pp'=>10,'priority'=>4,'makes_contact'=>false,'description'=>null,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>42, 'name_ja'=>'おいかぜ',         'name_en'=>'tailwind',        'type'=>'flying',   'category'=>'status',   'power'=>null,'accuracy'=>null,'pp'=>15,'priority'=>0,'makes_contact'=>false,'description'=>null,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>43, 'name_ja'=>'トリックルーム',   'name_en'=>'trick room',      'type'=>'psychic',  'category'=>'status',   'power'=>null,'accuracy'=>null,'pp'=>5, 'priority'=>-7,'makes_contact'=>false,'description'=>null,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>44, 'name_ja'=>'でんじは',         'name_en'=>'thunder wave',    'type'=>'electric', 'category'=>'status',   'power'=>null,'accuracy'=>90, 'pp'=>20, 'priority'=>0,'makes_contact'=>false,'description'=>null,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>45, 'name_ja'=>'こうごうせい',     'name_en'=>'synthesis',       'type'=>'grass',    'category'=>'status',   'power'=>null,'accuracy'=>null,'pp'=>5, 'priority'=>0,'makes_contact'=>false,'description'=>null,'created_at'=>$now,'updated_at'=>$now],
        ]);
    }
}
