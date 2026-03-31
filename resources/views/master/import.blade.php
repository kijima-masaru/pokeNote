@extends('layouts.app')
@section('title', 'PokeAPI インポート')
@section('content')
<div x-data="pokeImport()">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-cloud-download"></i> PokeAPI インポート</h4>
        <small class="text-muted">現在: ポケモン {{ $pokemonCount }}体 / わざ {{ $moveCount }}個</small>
    </div>

    <div class="row g-3">
        <!-- ポケモン1体インポート -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-success text-white">
                    <strong><i class="bi bi-person-plus"></i> ポケモン 1体インポート</strong>
                </div>
                <div class="card-body">
                    <p class="text-muted" style="font-size:.85rem">図鑑番号または英語名を入力してPokeAPIから取得します。</p>
                    <div class="input-group mb-2">
                        <input type="text" class="form-control" x-model="singlePokemon"
                               placeholder="例: 6 または charizard"
                               @keydown.enter="importSinglePokemon()">
                        <button class="btn btn-success" @click="importSinglePokemon()" :disabled="loading.single">
                            <span x-show="loading.single" class="spinner-border spinner-border-sm me-1"></span>
                            取得
                        </button>
                    </div>
                    <template x-if="results.single">
                        <div class="alert py-2 mb-0"
                             :class="results.single.error ? 'alert-danger' : 'alert-success'"
                             style="font-size:.85rem">
                            <template x-if="!results.single.error">
                                <span>
                                    <img :src="results.single.sprite_url" style="width:32px;height:32px;object-fit:contain" class="me-1">
                                    <strong x-text="results.single.name_ja"></strong> (#<span x-text="results.single.pokedex_number"></span>) を登録しました
                                </span>
                            </template>
                            <span x-show="results.single.error" x-text="results.single.error"></span>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- ポケモン一括インポート -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <strong><i class="bi bi-collection"></i> ポケモン 一括インポート</strong>
                </div>
                <div class="card-body">
                    <p class="text-muted" style="font-size:.85rem">図鑑番号の範囲を指定して一括取得します。最大50件ずつ。</p>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label style="font-size:.8rem">開始番号</label>
                            <input type="number" class="form-control form-control-sm" x-model.number="bulkFrom" min="1" max="1025">
                        </div>
                        <div class="col-6">
                            <label style="font-size:.8rem">終了番号</label>
                            <input type="number" class="form-control form-control-sm" x-model.number="bulkTo" min="1" max="1025">
                        </div>
                    </div>
                    <div class="mb-2">
                        <div class="d-flex gap-1 flex-wrap mb-1">
                            <button class="btn btn-sm btn-outline-secondary" @click="bulkFrom=1;bulkTo=50">第1世代前半 (1-50)</button>
                            <button class="btn btn-sm btn-outline-secondary" @click="bulkFrom=1;bulkTo=151">第1世代 (1-151)</button>
                            <button class="btn btn-sm btn-outline-secondary" @click="bulkFrom=152;bulkTo=251">第2世代 (152-251)</button>
                        </div>
                    </div>
                    <button class="btn btn-primary w-100" @click="importBulkPokemon()" :disabled="loading.bulk">
                        <span x-show="loading.bulk" class="spinner-border spinner-border-sm me-1"></span>
                        <span x-text="loading.bulk ? '取得中...' : `${bulkFrom}〜${bulkTo}番 を一括取得`"></span>
                    </button>
                    <template x-if="results.bulk">
                        <div class="mt-2">
                            <div class="alert alert-success py-2 mb-1" style="font-size:.85rem" x-show="results.bulk.success?.length > 0">
                                <i class="bi bi-check-circle"></i> 成功: <strong x-text="results.bulk.success?.length"></strong>件
                                (<span x-text="results.bulk.success?.map(p=>p.name).join('、')"></span>)
                            </div>
                            <div class="alert alert-warning py-2 mb-0" style="font-size:.85rem" x-show="results.bulk.failed?.length > 0">
                                <i class="bi bi-exclamation-triangle"></i> 失敗: <strong x-text="results.bulk.failed?.length"></strong>件
                                (No. <span x-text="results.bulk.failed?.join('、')"></span>)
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- わざインポート -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-warning text-dark">
                    <strong><i class="bi bi-lightning-charge"></i> わざ インポート</strong>
                </div>
                <div class="card-body">
                    <p class="text-muted" style="font-size:.85rem">わざの英語名を入力してPokeAPIから取得します。</p>
                    <div class="input-group mb-2">
                        <input type="text" class="form-control" x-model="singleMove"
                               placeholder="例: flamethrower"
                               @keydown.enter="importSingleMove()">
                        <button class="btn btn-warning" @click="importSingleMove()" :disabled="loading.move">
                            <span x-show="loading.move" class="spinner-border spinner-border-sm me-1"></span>
                            取得
                        </button>
                    </div>
                    <template x-if="results.move">
                        <div class="alert py-2 mb-0"
                             :class="results.move.error ? 'alert-danger' : 'alert-success'"
                             style="font-size:.85rem">
                            <template x-if="!results.move.error">
                                <span>
                                    <strong x-text="results.move.name_ja"></strong>
                                    （<span x-text="results.move.type"></span> / <span x-text="results.move.category"></span> / 威力<span x-text="results.move.power||'-'"></span>）を登録しました
                                </span>
                            </template>
                            <span x-show="results.move.error" x-text="results.move.error"></span>
                        </div>
                    </template>
                    <hr class="my-2">
                    <p class="text-muted mb-1" style="font-size:.8rem">よく使うわざ（英語名）：</p>
                    <div class="d-flex flex-wrap gap-1">
                        @foreach(['flamethrower','hydro-pump','thunderbolt','psychic','earthquake','close-combat','dragon-dance','protect','shadow-ball','moonblast'] as $move)
                            <button class="btn btn-sm btn-outline-secondary" style="font-size:.7rem"
                                    @click="singleMove='{{ $move }}'; importSingleMove()">{{ $move }}</button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 進化チェーンインポート -->
    <div class="row g-3 mt-0">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-info text-white">
                    <strong><i class="bi bi-arrow-right-circle"></i> 進化チェーン インポート</strong>
                </div>
                <div class="card-body">
                    <p class="text-muted" style="font-size:.85rem">図鑑番号を入力してそのポケモンの進化チェーンをインポートします。<br>※ 進化前後のポケモンが先にインポートされている必要があります。</p>
                    <div class="input-group mb-2">
                        <input type="number" class="form-control" x-model.number="evoPokedexNum"
                               placeholder="図鑑番号 (例: 4)" min="1" max="1025"
                               @keydown.enter="importEvolutions()">
                        <button class="btn btn-info text-white" @click="importEvolutions()" :disabled="loading.evo">
                            <span x-show="loading.evo" class="spinner-border spinner-border-sm me-1"></span>
                            取得
                        </button>
                    </div>
                    <template x-if="results.evo">
                        <div class="alert py-2 mb-0"
                             :class="results.evo.error ? 'alert-danger' : 'alert-success'"
                             style="font-size:.85rem">
                            <span x-text="results.evo.error || results.evo.message"></span>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- 進行ログ -->
    <div class="mt-3" x-show="log.length > 0">
        <h6 class="text-muted">実行ログ</h6>
        <div class="bg-dark text-light rounded p-2" style="max-height:200px;overflow-y:auto;font-size:.8rem;font-family:monospace">
            <template x-for="(line, i) in log" :key="i">
                <div x-text="line"></div>
            </template>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
function pokeImport() {
    return {
        singlePokemon: '', bulkFrom: 1, bulkTo: 151, singleMove: '', evoPokedexNum: '',
        loading: {single: false, bulk: false, move: false, evo: false},
        results: {single: null, bulk: null, move: null, evo: null},
        log: [],

        addLog(msg) { this.log.unshift(`[${new Date().toLocaleTimeString()}] ${msg}`); },

        // 共通ヘッダー
        headers() {
            return {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            };
        },

        // レスポンスをJSONとして安全に取得
        async safeJson(res) {
            const text = await res.text();
            try {
                return JSON.parse(text);
            } catch {
                console.error('Non-JSON response (HTTP ' + res.status + '):', text.slice(0, 300));
                return {error: `通信エラー (HTTP ${res.status})`};
            }
        },

        async importSinglePokemon() {
            if (!this.singlePokemon.trim()) return;
            this.loading.single = true;
            this.results.single = null;
            try {
                const res = await fetch('/api/v1/import/pokemon', {
                    method: 'POST',
                    headers: this.headers(),
                    body: JSON.stringify({id_or_name: this.singlePokemon}),
                });
                const data = await this.safeJson(res);
                this.results.single = data;
                if (!data.error) this.addLog(`✓ ${data.name_ja} (#${data.pokedex_number}) インポート完了`);
                else this.addLog(`✗ エラー: ${data.error}`);
            } catch (e) { this.results.single = {error: e.message}; }
            this.loading.single = false;
        },

        async importBulkPokemon() {
            this.loading.bulk = true;
            this.results.bulk = null;
            this.addLog(`一括インポート開始: No.${this.bulkFrom}〜${this.bulkTo}`);
            try {
                const res = await fetch('/api/v1/import/pokemon/bulk', {
                    method: 'POST',
                    headers: this.headers(),
                    body: JSON.stringify({from: this.bulkFrom, to: this.bulkTo}),
                });
                const data = await this.safeJson(res);
                this.results.bulk = data;
                if (!data.error) this.addLog(`✓ 成功: ${data.success?.length}件, 失敗: ${data.failed?.length}件`);
                else this.addLog(`✗ エラー: ${data.error}`);
            } catch (e) { this.addLog(`✗ エラー: ${e.message}`); }
            this.loading.bulk = false;
        },

        async importSingleMove() {
            if (!this.singleMove.trim()) return;
            this.loading.move = true;
            this.results.move = null;
            try {
                const res = await fetch('/api/v1/import/move', {
                    method: 'POST',
                    headers: this.headers(),
                    body: JSON.stringify({id_or_name: this.singleMove}),
                });
                const data = await this.safeJson(res);
                this.results.move = data;
                if (!data.error) this.addLog(`✓ わざ「${data.name_ja}」インポート完了`);
                else this.addLog(`✗ エラー: ${data.error}`);
            } catch (e) { this.results.move = {error: e.message}; }
            this.loading.move = false;
        },

        async importEvolutions() {
            if (!this.evoPokedexNum) return;
            this.loading.evo = true;
            this.results.evo = null;
            try {
                const res = await fetch('/api/v1/import/evolutions', {
                    method: 'POST',
                    headers: this.headers(),
                    body: JSON.stringify({pokemon_id: this.evoPokedexNum}),
                });
                const data = await this.safeJson(res);
                this.results.evo = data;
                if (!data.error) this.addLog(`✓ 進化チェーンインポート完了: ${data.count}件`);
                else this.addLog(`✗ エラー: ${data.error}`);
            } catch (e) { this.results.evo = {error: e.message}; }
            this.loading.evo = false;
        },
    };
}
</script>
@endpush
