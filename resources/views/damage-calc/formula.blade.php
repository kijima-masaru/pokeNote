@extends('layouts.app')
@section('title', 'ダメージ計算式の解説')
@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('damage-calc.index') }}">ダメージ計算</a></li>
        <li class="breadcrumb-item active">計算式の解説</li>
    </ol>
</nav>
<h4 class="mb-4"><i class="bi bi-info-circle"></i> ダメージ計算式の詳細解説</h4>

<div class="row g-4">
    {{-- 基本式 --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-dark text-white"><strong>基本ダメージ計算式</strong></div>
            <div class="card-body">
                <div class="bg-light rounded p-3 font-monospace mb-3" style="font-size:.9rem">
                    ダメージ = floor(floor(floor(floor(floor(床((攻撃側レベル × 2 / 5) + 2) × 技威力 × 攻撃実数値 / 防御実数値) / 50) + 2) × 修正値))
                </div>
                <p class="text-muted mb-0" style="font-size:.9rem">
                    ※「床」は切り捨て（floor）を意味します。計算の各段階で切り捨てが行われます。
                </p>
            </div>
        </div>
    </div>

    {{-- 実数値計算 --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-success text-white"><strong>実数値の計算式</strong></div>
            <div class="card-body">
                <h6>HP</h6>
                <div class="bg-light rounded p-2 font-monospace mb-3" style="font-size:.85rem">
                    HP = floor((種族値×2 + 個体値 + floor(努力値/4)) × レベル/100) + レベル + 10
                </div>
                <h6>HP以外のステータス</h6>
                <div class="bg-light rounded p-2 font-monospace mb-3" style="font-size:.85rem">
                    ステータス = floor((floor((種族値×2 + 個体値 + floor(努力値/4)) × レベル/100) + 5) × 性格補正)
                </div>
                <h6>性格補正</h6>
                <table class="table table-sm table-bordered" style="font-size:.85rem">
                    <tr><td>上昇性格（×1.1）</td><td>対応ステータスが 10% 上昇</td></tr>
                    <tr><td>下降性格（×0.9）</td><td>対応ステータスが 10% 下降</td></tr>
                    <tr><td>無補正（×1.0）</td><td>補正なし</td></tr>
                </table>
                <h6>努力値上限</h6>
                <p class="text-muted mb-0" style="font-size:.85rem">1ステータスあたり最大252、合計510まで。4振りで実数値が1上がります。</p>
            </div>
        </div>
    </div>

    {{-- ランク補正 --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-primary text-white"><strong>ランク補正</strong></div>
            <div class="card-body">
                <p style="font-size:.85rem">ランクは -6〜+6 の範囲で変化します。</p>
                <div class="bg-light rounded p-2 font-monospace mb-2" style="font-size:.85rem">
                    ランク &gt; 0 のとき: (2 + ランク) / 2<br>
                    ランク &lt; 0 のとき: 2 / (2 - ランク)<br>
                    ランク = 0 のとき: 1.0（補正なし）
                </div>
                <table class="table table-sm table-bordered" style="font-size:.82rem">
                    <thead class="table-light"><tr><th>ランク</th><th>倍率</th><th>ランク</th><th>倍率</th></tr></thead>
                    <tbody>
                        @foreach([[-6,'0.25'],[-5,'0.29'],[-4,'0.33'],[-3,'0.40'],[-2,'0.50'],[-1,'0.67']] as $r)
                        <tr><td>{{ $r[0] }}</td><td>× {{ $r[1] }}</td>
                        @endforeach
                        </tr>
                        @foreach([[1,'1.50'],[2,'2.00'],[3,'2.50'],[4,'3.00'],[5,'3.50'],[6,'4.00']] as $r)
                        <tr><td>+{{ $r[0] }}</td><td>× {{ $r[1] }}</td>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- タイプ相性 --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-info text-dark"><strong>タイプ相性補正</strong></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <h6>相性倍率</h6>
                        <table class="table table-sm table-bordered" style="font-size:.85rem">
                            <tr><td>こうかばつぐん（2倍）</td><td>× 2.0</td></tr>
                            <tr><td>2タイプともばつぐん</td><td>× 4.0</td></tr>
                            <tr><td>こうかいまひとつ（0.5倍）</td><td>× 0.5</td></tr>
                            <tr><td>2タイプともいまひとつ</td><td>× 0.25</td></tr>
                            <tr><td>こうかなし</td><td>× 0.0</td></tr>
                        </table>
                    </div>
                    <div class="col-md-4">
                        <h6>STAB（タイプ一致ボーナス）</h6>
                        <p style="font-size:.85rem">攻撃側のタイプと技タイプが一致する場合、ダメージに × 1.5 の補正がかかります。</p>
                        <div class="bg-light rounded p-2 font-monospace" style="font-size:.85rem">タイプ一致 → × 1.5</div>
                    </div>
                    <div class="col-md-4">
                        <h6>急所</h6>
                        <p style="font-size:.85rem">急所に当たった場合、以下の補正がかかります：</p>
                        <ul style="font-size:.85rem">
                            <li>ダメージ × 1.5</li>
                            <li>防御側の防御ランク上昇を無視</li>
                            <li>攻撃側の攻撃ランク下降を無視</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 天候・フィールド --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-warning text-dark"><strong>天候による補正</strong></div>
            <div class="card-body">
                <table class="table table-sm table-bordered" style="font-size:.85rem">
                    <thead class="table-light"><tr><th>天候</th><th>効果</th></tr></thead>
                    <tbody>
                        <tr><td>晴れ（にほんばれ）</td><td>ほのお技 × 1.5 / みず技 × 0.5</td></tr>
                        <tr><td>雨（あめふらし）</td><td>みず技 × 1.5 / ほのお技 × 0.5</td></tr>
                        <tr><td>砂嵐（すなあらし）</td><td>いわタイプの特防 × 1.5</td></tr>
                        <tr><td>雪（ゆき）</td><td>こおりタイプの防御 × 1.5 (ダブル用)</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- フィールド --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-success text-white"><strong>フィールドによる補正</strong></div>
            <div class="card-body">
                <table class="table table-sm table-bordered" style="font-size:.85rem">
                    <thead class="table-light"><tr><th>フィールド</th><th>効果</th></tr></thead>
                    <tbody>
                        <tr><td>グラスフィールド</td><td>くさ技 × 1.3（地上のポケモン）</td></tr>
                        <tr><td>エレキフィールド</td><td>でんき技 × 1.3（地上のポケモン）</td></tr>
                        <tr><td>サイコフィールド</td><td>エスパー技 × 1.3（地上のポケモン）</td></tr>
                        <tr><td>ミストフィールド</td><td>ドラゴン技 × 0.5（地上のポケモン）</td></tr>
                    </tbody>
                </table>
                <p class="text-muted mb-0" style="font-size:.8rem">※「地上」とはひこうタイプでなく、ふゆうでないポケモンを指します。</p>
            </div>
        </div>
    </div>

    {{-- その他修正 --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-secondary text-white"><strong>その他の補正</strong></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <h6>やけど</h6>
                        <p style="font-size:.85rem">やけど状態の攻撃側が物理技を使う場合、攻撃実数値に × 0.5 の補正がかかります。</p>
                    </div>
                    <div class="col-md-4">
                        <h6>乱数</h6>
                        <p style="font-size:.85rem">ダメージ計算では85〜100の乱数が16段階適用されます。最低乱数は × 0.85、最高乱数は × 1.0 です。</p>
                    </div>
                    <div class="col-md-4">
                        <h6>確定数の判定</h6>
                        <p style="font-size:.85rem">「確定1発」は最低乱数でも相手のHPを0以下にできる場合。「確定2発」は2回の最低乱数合計がHPを上回る場合です。</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
