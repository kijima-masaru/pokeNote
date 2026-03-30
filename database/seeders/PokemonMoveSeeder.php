<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PokemonMoveSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('pokemon_moves')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // pokemon_id => PokemonSeeder の id 列参照
        // move_id    => MoveSeeder の id 列参照
        // learn_method: level-up / machine / egg / tutor
        $rows = [
            // リザードン (pokemon_id=1)
            ['pokemon_id'=>1,'move_id'=>1, 'learn_method'=>'level-up','level_learned'=>50], // かえんほうしゃ
            ['pokemon_id'=>1,'move_id'=>2, 'learn_method'=>'machine', 'level_learned'=>null], // だいもんじ
            ['pokemon_id'=>1,'move_id'=>4, 'learn_method'=>'level-up','level_learned'=>62], // フレアドライブ
            ['pokemon_id'=>1,'move_id'=>8, 'learn_method'=>'level-up','level_learned'=>1],  // りゅうのまい
            ['pokemon_id'=>1,'move_id'=>20,'learn_method'=>'machine', 'level_learned'=>null], // りゅうのはどう
            ['pokemon_id'=>1,'move_id'=>29,'learn_method'=>'level-up','level_learned'=>1],  // そらをとぶ
            ['pokemon_id'=>1,'move_id'=>41,'learn_method'=>'machine', 'level_learned'=>null], // まもる

            // カメックス (pokemon_id=2)
            ['pokemon_id'=>2,'move_id'=>5, 'learn_method'=>'level-up','level_learned'=>58], // ハイドロポンプ
            ['pokemon_id'=>2,'move_id'=>6, 'learn_method'=>'machine', 'level_learned'=>null], // なみのり
            ['pokemon_id'=>2,'move_id'=>7, 'learn_method'=>'egg',     'level_learned'=>null], // アクアジェット
            ['pokemon_id'=>2,'move_id'=>21,'learn_method'=>'machine', 'level_learned'=>null], // じしん
            ['pokemon_id'=>2,'move_id'=>26,'learn_method'=>'machine', 'level_learned'=>null], // ラスターカノン
            ['pokemon_id'=>2,'move_id'=>13,'learn_method'=>'machine', 'level_learned'=>null], // れいとうビーム

            // フシギバナ (pokemon_id=3)
            ['pokemon_id'=>3,'move_id'=>35,'learn_method'=>'level-up','level_learned'=>45], // ギガドレイン
            ['pokemon_id'=>3,'move_id'=>36,'learn_method'=>'machine', 'level_learned'=>null], // エナジーボール
            ['pokemon_id'=>3,'move_id'=>45,'learn_method'=>'level-up','level_learned'=>1],  // こうごうせい
            ['pokemon_id'=>3,'move_id'=>15,'learn_method'=>'machine', 'level_learned'=>null], // サイコキネシス
            ['pokemon_id'=>3,'move_id'=>33,'learn_method'=>'egg',     'level_learned'=>null], // ムーンフォース
            ['pokemon_id'=>3,'move_id'=>41,'learn_method'=>'machine', 'level_learned'=>null], // まもる

            // ピカチュウ (pokemon_id=4)
            ['pokemon_id'=>4,'move_id'=>9, 'learn_method'=>'level-up','level_learned'=>26], // 10まんボルト
            ['pokemon_id'=>4,'move_id'=>10,'learn_method'=>'machine', 'level_learned'=>null], // かみなり
            ['pokemon_id'=>4,'move_id'=>11,'learn_method'=>'machine', 'level_learned'=>null], // ボルトチェンジ
            ['pokemon_id'=>4,'move_id'=>44,'learn_method'=>'level-up','level_learned'=>18], // でんじは
            ['pokemon_id'=>4,'move_id'=>39,'learn_method'=>'level-up','level_learned'=>1],  // からげんき

            // ミュウツー (pokemon_id=5)
            ['pokemon_id'=>5,'move_id'=>15,'learn_method'=>'level-up','level_learned'=>50], // サイコキネシス
            ['pokemon_id'=>5,'move_id'=>16,'learn_method'=>'machine', 'level_learned'=>null], // サイコショック
            ['pokemon_id'=>5,'move_id'=>17,'learn_method'=>'machine', 'level_learned'=>null], // シャドーボール
            ['pokemon_id'=>5,'move_id'=>19,'learn_method'=>'tutor',   'level_learned'=>null], // りゅうせいぐん
            ['pokemon_id'=>5,'move_id'=>43,'learn_method'=>'level-up','level_learned'=>1],  // トリックルーム
            ['pokemon_id'=>5,'move_id'=>38,'learn_method'=>'machine', 'level_learned'=>null], // はかいこうせん

            // カビゴン (pokemon_id=6)
            ['pokemon_id'=>6,'move_id'=>38,'learn_method'=>'machine', 'level_learned'=>null], // はかいこうせん
            ['pokemon_id'=>6,'move_id'=>37,'learn_method'=>'level-up','level_learned'=>1],  // ねこだまし
            ['pokemon_id'=>6,'move_id'=>39,'learn_method'=>'level-up','level_learned'=>1],  // からげんき
            ['pokemon_id'=>6,'move_id'=>21,'learn_method'=>'machine', 'level_learned'=>null], // じしん
            ['pokemon_id'=>6,'move_id'=>25,'learn_method'=>'machine', 'level_learned'=>null], // アイアンヘッド
            ['pokemon_id'=>6,'move_id'=>41,'learn_method'=>'machine', 'level_learned'=>null], // まもる

            // カイリュー (pokemon_id=7)
            ['pokemon_id'=>7,'move_id'=>8, 'learn_method'=>'level-up','level_learned'=>55], // りゅうのまい
            ['pokemon_id'=>7,'move_id'=>19,'learn_method'=>'level-up','level_learned'=>1],  // りゅうせいぐん
            ['pokemon_id'=>7,'move_id'=>29,'learn_method'=>'machine', 'level_learned'=>null], // そらをとぶ
            ['pokemon_id'=>7,'move_id'=>27,'learn_method'=>'machine', 'level_learned'=>null], // インファイト
            ['pokemon_id'=>7,'move_id'=>21,'learn_method'=>'machine', 'level_learned'=>null], // じしん
            ['pokemon_id'=>7,'move_id'=>13,'learn_method'=>'machine', 'level_learned'=>null], // れいとうビーム

            // バンギラス (pokemon_id=8)
            ['pokemon_id'=>8,'move_id'=>23,'learn_method'=>'level-up','level_learned'=>61], // ストーンエッジ
            ['pokemon_id'=>8,'move_id'=>21,'learn_method'=>'machine', 'level_learned'=>null], // じしん
            ['pokemon_id'=>8,'move_id'=>32,'learn_method'=>'level-up','level_learned'=>1],  // つじぎり
            ['pokemon_id'=>8,'move_id'=>25,'learn_method'=>'machine', 'level_learned'=>null], // アイアンヘッド
            ['pokemon_id'=>8,'move_id'=>27,'learn_method'=>'machine', 'level_learned'=>null], // インファイト

            // メタグロス (pokemon_id=9)
            ['pokemon_id'=>9,'move_id'=>25,'learn_method'=>'level-up','level_learned'=>50], // アイアンヘッド
            ['pokemon_id'=>9,'move_id'=>26,'learn_method'=>'level-up','level_learned'=>1],  // ラスターカノン
            ['pokemon_id'=>9,'move_id'=>15,'learn_method'=>'machine', 'level_learned'=>null], // サイコキネシス
            ['pokemon_id'=>9,'move_id'=>21,'learn_method'=>'machine', 'level_learned'=>null], // じしん
            ['pokemon_id'=>9,'move_id'=>27,'learn_method'=>'machine', 'level_learned'=>null], // インファイト

            // ガブリアス (pokemon_id=10)
            ['pokemon_id'=>10,'move_id'=>8, 'learn_method'=>'level-up','level_learned'=>48], // りゅうのまい
            ['pokemon_id'=>10,'move_id'=>20,'learn_method'=>'level-up','level_learned'=>1],  // りゅうのはどう
            ['pokemon_id'=>10,'move_id'=>21,'learn_method'=>'level-up','level_learned'=>33], // じしん
            ['pokemon_id'=>10,'move_id'=>23,'learn_method'=>'machine', 'level_learned'=>null], // ストーンエッジ
            ['pokemon_id'=>10,'move_id'=>30,'learn_method'=>'machine', 'level_learned'=>null], // つばめがえし

            // サーナイト (pokemon_id=20)
            ['pokemon_id'=>20,'move_id'=>15,'learn_method'=>'level-up','level_learned'=>57], // サイコキネシス
            ['pokemon_id'=>20,'move_id'=>16,'learn_method'=>'machine', 'level_learned'=>null], // サイコショック
            ['pokemon_id'=>20,'move_id'=>17,'learn_method'=>'machine', 'level_learned'=>null], // シャドーボール
            ['pokemon_id'=>20,'move_id'=>33,'learn_method'=>'level-up','level_learned'=>50], // ムーンフォース
            ['pokemon_id'=>20,'move_id'=>34,'learn_method'=>'machine', 'level_learned'=>null], // マジカルシャイン
            ['pokemon_id'=>20,'move_id'=>43,'learn_method'=>'tutor',   'level_learned'=>null], // トリックルーム
        ];

        DB::table('pokemon_moves')->insert($rows);
    }
}
