<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AbilitySeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('abilities')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $now = now();
        DB::table('abilities')->insert([
            ['id'=>1,  'name_ja'=>'もうか',         'name_en'=>'blaze',          'description'=>'HPが1/3以下になると、ほのおタイプの技の威力が1.5倍になる。', 'created_at'=>$now,'updated_at'=>$now],
            ['id'=>2,  'name_ja'=>'げきりゅう',     'name_en'=>'torrent',        'description'=>'HPが1/3以下になると、みずタイプの技の威力が1.5倍になる。', 'created_at'=>$now,'updated_at'=>$now],
            ['id'=>3,  'name_ja'=>'しんりょく',     'name_en'=>'overgrow',       'description'=>'HPが1/3以下になると、くさタイプの技の威力が1.5倍になる。', 'created_at'=>$now,'updated_at'=>$now],
            ['id'=>4,  'name_ja'=>'むしのしらせ',   'name_en'=>'swarm',          'description'=>'HPが1/3以下になると、むしタイプの技の威力が1.5倍になる。', 'created_at'=>$now,'updated_at'=>$now],
            ['id'=>5,  'name_ja'=>'ゆきがくれ',     'name_en'=>'snow cloak',     'description'=>'あられの天気のとき、回避率が1.2倍になる。', 'created_at'=>$now,'updated_at'=>$now],
            ['id'=>6,  'name_ja'=>'すなおこし',     'name_en'=>'sand stream',    'description'=>'登場したとき、天気をすなあらしにする。', 'created_at'=>$now,'updated_at'=>$now],
            ['id'=>7,  'name_ja'=>'ひでり',         'name_en'=>'drought',        'description'=>'登場したとき、天気をにほんばれにする。', 'created_at'=>$now,'updated_at'=>$now],
            ['id'=>8,  'name_ja'=>'あめふらし',     'name_en'=>'drizzle',        'description'=>'登場したとき、天気をあめにする。', 'created_at'=>$now,'updated_at'=>$now],
            ['id'=>9,  'name_ja'=>'ゆきふらし',     'name_en'=>'snow warning',   'description'=>'登場したとき、天気をゆきにする。', 'created_at'=>$now,'updated_at'=>$now],
            ['id'=>10, 'name_ja'=>'てんきや',       'name_en'=>'cloud nine',     'description'=>'天気の効果をなくす。', 'created_at'=>$now,'updated_at'=>$now],
            ['id'=>11, 'name_ja'=>'てんねん',       'name_en'=>'unaware',        'description'=>'相手の能力変化を無視して攻撃・被ダメを計算する。', 'created_at'=>$now,'updated_at'=>$now],
            ['id'=>12, 'name_ja'=>'いかく',         'name_en'=>'intimidate',     'description'=>'登場したとき、相手の攻撃を1段階下げる。', 'created_at'=>$now,'updated_at'=>$now],
            ['id'=>13, 'name_ja'=>'ちょすい',       'name_en'=>'water absorb',   'description'=>'みず技を受けるとHPが回復する。', 'created_at'=>$now,'updated_at'=>$now],
            ['id'=>14, 'name_ja'=>'でんきエンジン', 'name_en'=>'motor drive',    'description'=>'でんき技を受けると素早さが1段階上がる。', 'created_at'=>$now,'updated_at'=>$now],
            ['id'=>15, 'name_ja'=>'ふしぎなまもり', 'name_en'=>'wonder guard',   'description'=>'効果が抜群の技しかダメージを受けない。', 'created_at'=>$now,'updated_at'=>$now],
            ['id'=>16, 'name_ja'=>'きよめのしお',   'name_en'=>'purifying salt', 'description'=>'ゴーストタイプの技のダメージを半減し、状態異常にならない。', 'created_at'=>$now,'updated_at'=>$now],
            ['id'=>17, 'name_ja'=>'マルチスケイル', 'name_en'=>'multiscale',     'description'=>'HPが満タンのとき、受けるダメージが半分になる。', 'created_at'=>$now,'updated_at'=>$now],
            ['id'=>18, 'name_ja'=>'ふしぎなうろこ', 'name_en'=>'marvel scale',   'description'=>'状態異常のとき、防御が1.5倍になる。', 'created_at'=>$now,'updated_at'=>$now],
            ['id'=>19, 'name_ja'=>'プレッシャー',   'name_en'=>'pressure',       'description'=>'相手が使う技のPPを2消費させる。', 'created_at'=>$now,'updated_at'=>$now],
            ['id'=>20, 'name_ja'=>'トレース',       'name_en'=>'trace',          'description'=>'登場したとき、相手の特性をコピーする。', 'created_at'=>$now,'updated_at'=>$now],
            ['id'=>21, 'name_ja'=>'シンクロ',       'name_en'=>'synchronize',    'description'=>'状態異常になったとき、相手にも同じ状態異常を与える。', 'created_at'=>$now,'updated_at'=>$now],
            ['id'=>22, 'name_ja'=>'ほのおのからだ', 'name_en'=>'flame body',     'description'=>'直接攻撃を受けたとき、30%の確率で相手をやけどにする。', 'created_at'=>$now,'updated_at'=>$now],
            ['id'=>23, 'name_ja'=>'もらいび',       'name_en'=>'flash fire',     'description'=>'ほのお技を受けると、次のほのお技の威力が1.5倍になる。', 'created_at'=>$now,'updated_at'=>$now],
            ['id'=>24, 'name_ja'=>'せいでんき',     'name_en'=>'static',         'description'=>'直接攻撃を受けたとき、30%の確率で相手をまひにする。', 'created_at'=>$now,'updated_at'=>$now],
            ['id'=>25, 'name_ja'=>'ちからずく',     'name_en'=>'sheer force',    'description'=>'追加効果のある技の威力が1.3倍になるが、追加効果がなくなる。', 'created_at'=>$now,'updated_at'=>$now],
        ]);
    }
}
