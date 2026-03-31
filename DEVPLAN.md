# pokeNote 開発計画 (DEVPLAN.md)

> このファイルはClaude Codeが自動更新します。
> タスク完了時に `[x]` に変更し、新しいタスクを追記してください。

---

## ✅ 完了済み

### コア実装（全56ファイル）
- [x] Enums: BattleResult, MoveCategory, Nature, PokemonType
- [x] Models: Pokemon, Move, Ability, Item, CustomPokemon, Battle, Turn, PokemonType
- [x] Services: StatCalculatorService, DamageCalculatorService
- [x] API Controllers: Pokemon, Move, Ability, Item, CustomPokemon, Battle, Turn, DamageCalc
- [x] Web Controllers: Dashboard, Pokemon, CustomPokemon, DamageCalc, Battle
- [x] Requests: DamageCalcRequest, StoreBattleRequest, StoreCustomPokemonRequest, StoreTurnRequest
- [x] Routes: web.php, api.php (全エンドポイント)
- [x] Views: layouts/app, dashboard, pokemon/index+show, custom-pokemon/index+create+show+edit, damage-calc/index, battle/index+create+show
- [x] Migrations: 11テーブル

### 追加実装済み
- [x] Seeder: AbilitySeeder (25特性), ItemSeeder (25持ち物), MoveSeeder (45わざ), PokemonSeeder (20体), PokemonTypeSeeder, PokemonAbilitySeeder
- [x] DatabaseSeeder: 全Seederを順番に呼び出し
- [x] カスタムポケモン削除: index + show 画面に削除ボタン
- [x] 対戦履歴削除: index 画面に削除ボタン
- [x] 対戦メモ編集: show 画面でインライン編集
- [x] ターンインライン編集: show 画面でHP%・メモを編集
- [x] API CRUD拡張: Ability, Item, Move, Pokemon に POST/PUT/DELETE 追加
- [x] マスターデータ管理画面: master/abilities, items, moves, pokemon (4画面)
- [x] ダメージ計算アドホック入力: 「直接入力」モード追加、DamageCalcAdhocController, calculateFromRaw()
- [x] サイドバー: マスター管理セクション追加

---

## 🔲 未実装・次のタスク（優先順）

### 高優先度
- [x] **PokeAPI連携インポート** — `/master/import` 画面実装済み。PokeApiImportController 実装済み。
- [x] **エラーハンドリングUI** — abilities/items/moves/pokemon 全4画面にフィールドレベルエラー表示実装済み。
- [x] **ダメージ計算: わざ検索の修正** — x-if → x-show に変更、常時表示の検索ボックス、onMoveChange() 追加済み。
- [x] **マスターポケモンの画像を保存できるようにする** — pokemon/{id}/imageエンドポイント追加、master/pokemon.blade.phpに画像アップロードボタン追加
- [x] **ダッシュボード画面の追加（ダッシュボード画面はユーザーが好きな位置に好きな項目を設置できる画面）** — SortableJSでドラッグ並べ替え、チェックで表示/非表示、localStorageに保存
- [x] **対戦履歴の作成時に対戦相手のポケモンを設定できるようにする** — battle_opponent_pokemonテーブル追加、APIエンドポイント追加、show画面に6スロットUIを追加
- [x] **画面認識機能を追加** — ScreenshotRecognitionController実装。GD2による色ヒストグラム照合。battle/show画面に「画像認識」ボタン追加。ローカル保存スプライトと照合して候補を自動表示、クリックで相手ポケモンを自動選択。
- [x] **画面認識機能は対戦履歴の対戦相手のポケモン設定時に使用できるようにする** — 対戦詳細画面の相手ポケモンスロット編集モーダルに統合済み
- [x] **画面認識機能で認識した画面に映っているポケモンの画像とマスターポケモンの画像を照合し、対戦相手のポケモンを自動選択されるようにする** — 認識結果クリックでopponentSlotFormに自動セット
- [x] **ダメージ計算式の詳細閲覧ページを追加** — `/damage-calc/formula` ページ追加、計算式・実数値計算・ランク補正・タイプ相性・天候・フィールド・やけど・乱数を網羅
- [x] **ダメージ計算式（特性・天候・フィールドの状態などすべての可能性を含む）の詳細を文言化** — formula.blade.phpに全補正を文言で説明
- [x] **ダメージ計算結果をメモに残せるように設定** — 計算結果カードに「メモに追加」ボタン追加、ページ内メモリスト表示・削除・ラベル編集対応
- [x] **ドロップやドラッグなど直感的に操作できるように全画面のGUIを調整** — カスタムポケモン登録・編集画面の技スロットにSortableJSドラッグ並べ替え追加。ダッシュボードはウィジェット並べ替え済み。
- [x] **メガシンカポケモンを追加** — is_mega/base_pokemon_idカラム追加、MegaPokemonSeeder追加（9体）、図鑑一覧にメガフィルタ・バッジ追加
- [x] **道具の画像を保存できるようにする** — items/{id}/imageエンドポイント追加、items.blade.phpに画像表示・アップロードボタン追加

### 中優先度
- [x] **カスタムポケモン一覧: ページネーション改善** — Bootstrap 5ページネーション・名前検索フィルタ追加。
- [x] **ポケモン図鑑: わざ覚え方表示** — pokemon_movesにlearn_method/level_learnedカラム追加、PokemonMoveSeeder追加、show画面に覚え方バッジ表示。
- [x] **対戦履歴: 統計ダッシュボード** — 勝率・勝敗数・進捗バー・直近10戦バッジをdashboard/indexに表示。

### 低優先度
- [x] **テスト** — StatCalculatorServiceTest（8ケース）・DamageCalculatorServiceTest（17ケース）実装済み。PHPUnit\Framework\TestCase使用、DB不要。
- [x] **認証** — User モデル・users/sessions マイグレーション・LoginController・RegisterController・auth/login+register ビュー実装。全Webルートをauth ミドルウェアで保護。ナビバーにユーザー名+ログアウトドロップダウン追加。

---

## 🔲 フェーズ2タスク（追加機能）

### 高優先度
- [x] **ユーザーデータ分離** — custom_pokemon・battlesにuser_idカラム追加（マイグレーション）。全API・WebコントローラーをAuth::id()でスコープ。TurnController/BattleOpponentPokemonControllerも対戦の所有確認を追加。
- [x] **ポケモン比較機能** — `/compare` ページ追加。2体の種族値バー比較・差分バッジ・合計比較・特性一覧・共通わざ一覧・入れ替えボタン。Alpine.jsで動的検索。
- [x] **対戦相手別勝率統計** — battle/index に対戦相手別集計テーブル追加。勝率プログレスバー・色分け（60%↑緑/40%↑黄/それ以下赤）、上位10件表示。

### 中優先度
- [x] **わざ詳細ページ** — `/moves` 一覧・`/moves/{id}` 詳細ページ追加。基本情報・説明・覚えるポケモングリッド（覚え方バッジ付き）。サイドバー追加。ポケモン詳細のわざ名にリンク追加。
- [x] **パスワードリセット** — ForgotPasswordController・ResetPasswordController実装。forgot-password/reset-passwordビュー作成。ログイン画面に「パスワードをお忘れですか？」リンク追加。Laravel Password::sendResetLink/reset使用。
- [x] **カスタムポケモンのエクスポート/インポート** — CustomPokemonExportImportController実装。全体/個別エクスポート・JSONインポート（英語名でポケモン/特性/持ち物/わざを照合）。マイポケモン一覧にエクスポート/インポートボタン追加。

---

## 🔲 フェーズ3タスク（品質・UX改善）

### 高優先度
- [x] **プロフィール設定ページ** — ユーザー名・メールアドレス・パスワード変更ができる `/profile` ページを追加
- [x] **対戦履歴フィルタ・検索** — battle/index に相手名・結果・フォーマット・日付範囲でフィルタリングを追加
- [x] **マイポケモン詳細の強化** — custom-pokemon/show に実数値バーグラフ・技の詳細（タイプ・威力）を追加表示
- [ ] **GithubとClaude Codeを連携**
- [ ] **FigmaとClaude Codeを連携**

### 中優先度
- [x] **通知/フラッシュメッセージ改善** — 登録・更新・削除成功時にトースト通知（右下表示）を追加
- [x] **ポケモン図鑑: ソート機能** — 図鑑一覧に種族値合計・各ステータスでのソート機能を追加
- [x] **対戦タグ機能** — 対戦履歴にタグを付けて絞り込めるようにする

---

## 🔲 フェーズ4タスク（機能拡充）

### 高優先度
- [x] **マイポケモン: チームビルダー** — 最大6体でパーティを組み、タイプ相性の穴・役割を確認できる `/teams` ページ追加
- [x] **対戦統計グラフ** — 月別勝率推移・使用ポケモン頻度のChart.jsグラフをダッシュボードに追加
- [x] **ポケモン図鑑: タイプ相性早見表** — 選択タイプへの攻撃/被弾倍率を一覧表示するページ追加

### 中優先度
- [x] **カスタムポケモン: コピー機能** — 既存のカスタムポケモンを複製してすぐ編集できるボタンを追加
- [x] **ターン記録: キーボードショートカット強化** — Tab/矢印キーでフォームフィールドを高速移動できるようにする

---

## 🔲 フェーズ5タスク（完成度向上）

### 高優先度
- [x] **対戦レポート** — 1対戦の振り返りページ（ターン数・HP推移グラフ・使用技頻度）追加
- [x] **マイポケモン: 素早さ比較** — 2体のポケモンを選んで素早さ実数値を比較し、先攻/後攻を判定するツール追加
- [x] **ポケモン図鑑: 検索強化** — タイプ複合検索・種族値範囲フィルタ追加

### 中優先度
- [x] **バルク登録** — CSVまたはJSONで複数のカスタムポケモンを一括登録できる機能
- [x] **対戦メモのマークダウン対応** — メモ欄でマークダウン記法をレンダリング

---

## 🔲 フェーズ6タスク（仕上げ・polish）

### 高優先度
- [x] **レスポンシブ対応強化** — モバイル表示でサイドバーをハンバーガーメニュー化、タッチ操作最適化
- [x] **ダークモード** — システム設定に連動したダークモード対応（Bootstrap dark themeを利用）
- [x] **ポケモン図鑑: お気に入り機能** — 図鑑からお気に入りに追加し、マイポケモン作成時に参照できる

### 中優先度
- [x] **検索履歴** — ポケモン図鑑・ダメージ計算の最近の検索をlocalStorageに保存して候補表示
- [x] **対戦記録: CSV出力** — 対戦一覧をCSVでダウンロードできるボタンを追加

---

---

## 🔲 フェーズ7タスク（発展機能）

### 高優先度
- [x] **対戦相手ポケモン: タイプ相性サマリー** — battle/show の相手ポケモン6体から弱点・耐性を集計し、自パーティのわざ選択に活かせるサマリーを表示
- [x] **ポケモン図鑑: 進化チェーン表示** — pokemon/show に進化前・進化後のポケモンを横並びリンクで表示
- [x] **カスタムポケモン: QRコードエクスポート** — JSON構成情報をQRコード化して他のユーザーと共有できる機能

### 中優先度
- [x] **ダメージ計算: 連続計算モード** — 複数の技・相手ポケモンをまとめて計算し、一覧比較できるモード
- [x] **対戦履歴: カレンダービュー** — 月カレンダー形式で対戦日を視覚化し、その日の試合をクリックで表示

---

## 📋 実装ルール（引き継ぎ用）

- Laravelバージョン: 11 / PHP: 8.2+
- フロントエンド: Blade + Alpine.js 3.x + Bootstrap 5.3
- API: `/api/v1/` プレフィックス、JSONレスポンス
- DB: MySQL 8.0、テーブル名は複数形（`custom_pokemon`のみ例外で`$table`明示）
- ファイル編集前に必ずReadツールで読む
- Write/Editツールは確認不要で実行してよい
- Testツールは確認不要で実行してよい
- `php artisan db:seed` でサンプルデータ投入可能

---

## 🔧 セットアップコマンド（未実行の場合）

```bash
php artisan migrate
php artisan db:seed
php artisan serve
```

### Docker

Laravel の `composer.json`・`public/`・`artisan` などフレームワーク一式がある前提で利用します。

```bash
cp .env.example .env
docker compose up -d --build
```

- **アプリ（nginx）**: `http://localhost`（既定ポート 80。`APP_PORT` で変更可）
- **phpMyAdmin**: `http://localhost:8080`（`PMA_PORT` で変更可）
- **MySQL**: ホストからは `127.0.0.1` + `FORWARD_DB_PORT`（既定 3306）。アプリ DB ユーザー既定は `MYSQL_APP_USER` / `MYSQL_APP_PASSWORD`（未設定時 `pokenote` / `secret`）。root は `DB_ROOT_PASSWORD`（既定 `root`）

---

## ✅ 追加実装（インフラ）

- [x] **Docker** — `docker-compose.yml`（nginx 1.21.1 + PHP 8.2 FPM + MySQL 8.0.26 + phpMyAdmin）、`docker/php/Dockerfile`、`docker/nginx/default.conf`、`docker/mysql/my.cnf`、`docker/php/docker-entrypoint.sh`
- [x] **Laravel 11 スケルトン** — `composer.json` / `composer.lock`、`artisan`、`public/index.php`、`bootstrap/app.php`（API ルート・ゲストは `login` へ）、`config/*`、`routes/console.php`、`app/Http/Controllers/Controller.php`、`app/Providers/AppServiceProvider.php`、`database/factories/UserFactory.php`、`phpunit.xml`、`vite` / `tailwind` / `postcss` 設定、`storage` / `bootstrap/cache` の `.gitignore`
