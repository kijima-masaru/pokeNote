# pokeNote - ポケモン対戦サポートアプリ

## 🤖 Claude Code 自動開発ルール

**このセクションはClaude Codeへの指示です。**

### 会話開始時の自動アクション
ユーザーからメッセージを受け取ったとき（「続けて」「開発を進めて」「おはよう」など内容を問わず）、以下を実行してください：

1. `DEVPLAN.md` を読む
2. `## 🔲 未実装・次のタスク` セクションの **最上位の未完タスク** を特定する
3. ユーザーに確認を求めず、即座に実装を開始する
4. タスク完了後、`DEVPLAN.md` の該当行を `- [x]` に更新する
5. 次のタスクに自動的に移行する

### ファイル操作ルール
- Write / Edit ツールは確認不要で実行してよい
- ファイル編集前に必ずReadツールで現在の内容を確認する
- 実装後にDEVPLAN.mdを更新してタスクを完了済みにする

---

## プロジェクト概要

ポケモンチャンピオンズ（2025年発売予定）の対戦をサポートするWebアプリケーション。

## 技術スタック

| 項目 | 技術 |
|---|---|
| フレームワーク | Laravel 11 |
| バックエンド | PHP 8.2+ |
| フロントエンド | Blade + Alpine.js 3.x |
| CSSフレームワーク | Bootstrap 5.3 + Bootstrap Icons |
| データベース | MySQL 8.0 |
| APIスタイル | REST JSON API (`/api/v1/`) |

## 主要機能

1. **ポケモン図鑑** - ポケモン・わざ・特性・持ち物のマスターデータ登録・閲覧
2. **マイポケモン** - 努力値・個体値・性格・技構成を含むカスタムポケモンの登録・閲覧・編集
3. **ダメージ計算** - 乱数16本・タイプ相性・天気/フィールド/ランク補正対応
4. **対戦ターン履歴** - HP残量スライダー・Ctrl+Enterで高速入力、対戦ごとの結果記録

## ディレクトリ構造

```
pokeNote/
├── app/
│   ├── Enums/
│   │   ├── PokemonType.php      # 18タイプ（label()メソッドで日本語名）
│   │   ├── Nature.php           # 25性格（boostedStat/reducedStat メソッド）
│   │   ├── MoveCategory.php     # physical/special/status
│   │   └── BattleResult.php     # win/lose/draw
│   ├── Models/
│   │   ├── Pokemon.php          # base_total アクセサ付き
│   │   ├── PokemonType.php      # pokemon_typesテーブル（timestamps=false）
│   │   ├── Move.php
│   │   ├── Ability.php
│   │   ├── Item.php
│   │   ├── CustomPokemon.php    # actual_stats・display_name アクセサ付き
│   │   ├── Battle.php
│   │   └── Turn.php
│   ├── Services/
│   │   ├── StatCalculatorService.php    # 実数値計算（公式通り）
│   │   └── DamageCalculatorService.php # ダメージ計算（タイプ相性表内蔵）
│   └── Http/
│       ├── Controllers/Api/     # JSON API コントローラ群
│       ├── Controllers/Web/     # Blade描画コントローラ群
│       └── Requests/            # FormRequest バリデーション
├── database/migrations/         # 11テーブル分のマイグレーション
├── resources/views/
│   ├── layouts/app.blade.php    # 共通レイアウト（サイドバー付き）
│   ├── dashboard/index.blade.php
│   ├── pokemon/{index,show}.blade.php
│   ├── custom-pokemon/{index,create,show,edit}.blade.php
│   ├── damage-calc/index.blade.php
│   └── battle/{index,create,show}.blade.php
└── routes/
    ├── web.php                  # Webページルート
    └── api.php                  # /api/v1/ APIルート
```

## DBテーブル設計（11テーブル）

| テーブル | 用途 |
|---|---|
| `pokemon` | ポケモンマスター（種族値・スプライトURL） |
| `moves` | わざマスター（タイプ・分類・威力・命中・PP） |
| `abilities` | 特性マスター |
| `items` | 持ち物マスター |
| `pokemon_types` | ポケモン↔タイプ（slot:1=タイプ1, 2=タイプ2） |
| `pokemon_abilities` | ポケモン↔特性（slot:3=夢特性） |
| `pokemon_moves` | ポケモン↔覚えるわざ |
| `custom_pokemon` | カスタムポケモン（IV/EV/性格/技構成） |
| `custom_pokemon_moves` | カスタムポケモン↔わざ（slot:1-4） |
| `battles` | 対戦セッション（結果・相手名・フォーマット） |
| `turns` | 対戦ターン履歴（HP残量%・使用わざ） |

## APIエンドポイント一覧

### マスターデータ（GET のみ）
```
GET /api/v1/pokemon          ?name=&type=&per_page=
GET /api/v1/pokemon/{id}
GET /api/v1/moves            ?name=&type=&category=
GET /api/v1/moves/{id}
GET /api/v1/abilities        ?name=
GET /api/v1/abilities/{id}
GET /api/v1/items            ?name=
GET /api/v1/items/{id}
```

### カスタムポケモン（CRUD）
```
GET|POST        /api/v1/custom-pokemon
GET|PUT|DELETE  /api/v1/custom-pokemon/{id}
```

POSTボディ:
```json
{
  "pokemon_id": 1, "ability_id": 1, "item_id": null,
  "nature": "timid", "level": 50,
  "ivs": {"hp":31,"attack":31,"defense":31,"sp_attack":31,"sp_defense":31,"speed":31},
  "evs": {"hp":0,"attack":0,"defense":0,"sp_attack":252,"sp_defense":4,"speed":252},
  "move_ids": [1,2,3,4], "nickname": "ニックネーム", "memo": "メモ"
}
```

### ダメージ計算
```
POST /api/v1/damage-calc
```
ボディ: `attacker_id, defender_id, move_id, attacker_rank, defender_rank, weather, terrain, is_critical, other_modifiers`

レスポンス: `damage_min, damage_max, damage_percent_min/max, one_shot, two_shot, type_effectiveness, rolls[16]`

### 対戦・ターン（CRUD）
```
GET|POST        /api/v1/battles
GET|PUT|DELETE  /api/v1/battles/{id}
GET|POST        /api/v1/battles/{battleId}/turns
PUT|DELETE      /api/v1/battles/{battleId}/turns/{turnNumber}
```

## Webページ構成

| URL | ビュー | 説明 |
|---|---|---|
| `/` | dashboard/index | ダッシュボード |
| `/pokemon` | pokemon/index | 図鑑一覧（Alpine.js インクリメンタルサーチ） |
| `/pokemon/{id}` | pokemon/show | 詳細・種族値バー・わざ一覧 |
| `/custom-pokemon` | custom-pokemon/index | マイポケモン一覧 |
| `/custom-pokemon/create` | custom-pokemon/create | 登録フォーム（実数値リアルタイムプレビュー） |
| `/custom-pokemon/{id}` | custom-pokemon/show | 詳細・実数値表示 |
| `/custom-pokemon/{id}/edit` | custom-pokemon/edit | 編集フォーム |
| `/damage-calc` | damage-calc/index | ダメージ計算（?attacker=&defender= で初期選択） |
| `/battles` | battle/index | 対戦履歴一覧 |
| `/battles/create` | battle/create | 新規対戦作成 |
| `/battles/{id}` | battle/show | ターン記録（Ctrl+Enterで高速入力） |

## 主要なコーディングルール

### モデル
- `CustomPokemon` の `actual_stats` アクセサは `StatCalculatorService` を直接 `new` して呼び出す
- `PokemonType` モデルは `$timestamps = false`
- `custom_pokemon` テーブルは複数形にならないため `$table = 'custom_pokemon'` を明示

### サービス
- `StatCalculatorService::calcHp()` / `calcStat()` はポケモン公式の実数値計算式を使用
- `DamageCalculatorService` はタイプ相性表を `typeChart()` メソッドに内蔵
- ランク補正: `rankMultiplier(int $rank)` — 正なら `(2+rank)/2`、負なら `2/(2-rank)`

### フロントエンド
- Alpine.js の `x-data` でコンポーネント化（`customPokemonForm()`, `damageCalc()`, `battleShow()` など）
- API呼び出しはすべて `fetch()` + `X-CSRF-TOKEN` ヘッダー
- 性格補正テーブルはフロントエンドJSにも `natureBoosts` オブジェクトとして実装（実数値リアルタイムプレビュー用）
- タイプバッジは `.type-{type}` CSSクラスで色分け（`layouts/app.blade.php` に定義）

## セットアップ手順

```bash
# 1. Laravelプロジェクト初期化
composer create-project laravel/laravel . --prefer-dist

# 2. 環境設定
cp .env.example .env
php artisan key:generate

# 3. .env のDB設定を編集
#    DB_DATABASE=poke_note
#    DB_USERNAME=root
#    DB_PASSWORD=xxx

# 4. DB作成
mysql -u root -p -e "CREATE DATABASE poke_note CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 5. マイグレーション
php artisan migrate

# 6. 起動
php artisan serve
# → http://localhost:8000
```

## 今後の実装予定

DEVPLAN.mdの実装タスクを確認する