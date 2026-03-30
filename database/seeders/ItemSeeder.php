<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('items')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $now = now();
        DB::table('items')->insert([
            ['id'=>1,  'name_ja'=>'こだわりハチマキ', 'name_en'=>'choice band',    'category'=>'boost',    'description'=>'ぶつり技の威力が1.5倍になる。ただし、選んだ技しか使えなくなる。', 'created_at'=>$now,'updated_at'=>$now],
            ['id'=>2,  'name_ja'=>'こだわりメガネ',   'name_en'=>'choice specs',   'category'=>'boost',    'description'=>'とくしゅ技の威力が1.5倍になる。ただし、選んだ技しか使えなくなる。', 'created_at'=>$now,'updated_at'=>$now],
            ['id'=>3,  'name_ja'=>'こだわりスカーフ', 'name_en'=>'choice scarf',   'category'=>'boost',    'description'=>'素早さが1.5倍になる。ただし、選んだ技しか使えなくなる。', 'created_at'=>$now,'updated_at'=>$now],
            ['id'=>4,  'name_ja'=>'いのちのたま',     'name_en'=>'life orb',       'category'=>'boost',    'description'=>'技の威力が1.3倍になる。ただし、使うたびにHPが1/10削られる。', 'created_at'=>$now,'updated_at'=>$now],
            ['id'=>5,  'name_ja'=>'たつじんのおび',   'name_en'=>'expert belt',    'category'=>'boost',    'description'=>'効果が抜群の技の威力が1.2倍になる。', 'created_at'=>$now,'updated_at'=>$now],
            ['id'=>6,  'name_ja'=>'しろいハーブ',     'name_en'=>'white herb',     'category'=>'other',    'description'=>'能力が下がったとき、一度だけ元に戻す。', 'created_at'=>$now,'updated_at'=>$now],
            ['id'=>7,  'name_ja'=>'ひかりのこな',     'name_en'=>'bright powder',  'category'=>'other',    'description'=>'相手の技の命中率を下げる。', 'created_at'=>$now,'updated_at'=>$now],
            ['id'=>8,  'name_ja'=>'きあいのタスキ',   'name_en'=>'focus sash',     'category'=>'other',    'description'=>'HPが満タンのとき、一撃で倒される攻撃をHP1で耐える。', 'created_at'=>$now,'updated_at'=>$now],
            ['id'=>9,  'name_ja'=>'オボンのみ',       'name_en'=>'sitrus berry',   'category'=>'berry',    'description'=>'HPが半分以下になったとき、HP1/4を回復する。', 'created_at'=>$now,'updated_at'=>$now],
            ['id'=>10, 'name_ja'=>'チイラのみ',       'name_en'=>'salac berry',    'category'=>'berry',    'description'=>'HPが1/4以下になったとき、素早さが1段階上がる。', 'created_at'=>$now,'updated_at'=>$now],
            ['id'=>11, 'name_ja'=>'ヤタピのみ',       'name_en'=>'petaya berry',   'category'=>'berry',    'description'=>'HPが1/4以下になったとき、特攻が1段階上がる。', 'created_at'=>$now,'updated_at'=>$now],
            ['id'=>12, 'name_ja'=>'たべのこし',       'name_en'=>'leftovers',      'category'=>'recovery', 'description'=>'毎ターン終了時にHPを1/16回復する。', 'created_at'=>$now,'updated_at'=>$now],
            ['id'=>13, 'name_ja'=>'くろいてっきゅう', 'name_en'=>'iron ball',      'category'=>'other',    'description'=>'素早さが半分になる。じめん技を受けるようになる。', 'created_at'=>$now,'updated_at'=>$now],
            ['id'=>14, 'name_ja'=>'するどいくちばし', 'name_en'=>'sharp beak',     'category'=>'boost',    'description'=>'ひこうタイプの技の威力が1.2倍になる。', 'created_at'=>$now,'updated_at'=>$now],
            ['id'=>15, 'name_ja'=>'まがったスプーン', 'name_en'=>'twisted spoon',  'category'=>'boost',    'description'=>'エスパータイプの技の威力が1.2倍になる。', 'created_at'=>$now,'updated_at'=>$now],
            ['id'=>16, 'name_ja'=>'くろおび',         'name_en'=>'black belt',     'category'=>'boost',    'description'=>'かくとうタイプの技の威力が1.2倍になる。', 'created_at'=>$now,'updated_at'=>$now],
            ['id'=>17, 'name_ja'=>'シルクのスカーフ', 'name_en'=>'silk scarf',     'category'=>'boost',    'description'=>'ノーマルタイプの技の威力が1.2倍になる。', 'created_at'=>$now,'updated_at'=>$now],
            ['id'=>18, 'name_ja'=>'もくたん',         'name_en'=>'charcoal',       'category'=>'boost',    'description'=>'ほのおタイプの技の威力が1.2倍になる。', 'created_at'=>$now,'updated_at'=>$now],
            ['id'=>19, 'name_ja'=>'しんぴのしずく',   'name_en'=>'mystic water',   'category'=>'boost',    'description'=>'みずタイプの技の威力が1.2倍になる。', 'created_at'=>$now,'updated_at'=>$now],
            ['id'=>20, 'name_ja'=>'じしゃく',         'name_en'=>'magnet',         'category'=>'boost',    'description'=>'でんきタイプの技の威力が1.2倍になる。', 'created_at'=>$now,'updated_at'=>$now],
            ['id'=>21, 'name_ja'=>'とけないこおり',   'name_en'=>'never-melt ice', 'category'=>'boost',    'description'=>'こおりタイプの技の威力が1.2倍になる。', 'created_at'=>$now,'updated_at'=>$now],
            ['id'=>22, 'name_ja'=>'どくバリ',         'name_en'=>'poison barb',    'category'=>'boost',    'description'=>'どくタイプの技の威力が1.2倍になる。', 'created_at'=>$now,'updated_at'=>$now],
            ['id'=>23, 'name_ja'=>'もののほん',       'name_en'=>'dragon fang',    'category'=>'boost',    'description'=>'ドラゴンタイプの技の威力が1.2倍になる。', 'created_at'=>$now,'updated_at'=>$now],
            ['id'=>24, 'name_ja'=>'くろいメガネ',     'name_en'=>'black glasses',  'category'=>'boost',    'description'=>'あくタイプの技の威力が1.2倍になる。', 'created_at'=>$now,'updated_at'=>$now],
            ['id'=>25, 'name_ja'=>'メタルコート',     'name_en'=>'metal coat',     'category'=>'boost',    'description'=>'はがねタイプの技の威力が1.2倍になる。', 'created_at'=>$now,'updated_at'=>$now],
        ]);
    }
}
