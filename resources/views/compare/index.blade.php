@extends('layouts.app')
@section('title', 'ポケモン比較')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-bar-chart-steps"></i> ポケモン比較</h4>
</div>

<div x-data="pokemonCompare()" class="row g-3">

    <!-- 左: ポケモンA選択 -->
    <div class="col-md-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white"><strong>ポケモン A</strong></div>
            <div class="card-body">
                <input type="text" class="form-control mb-2" placeholder="名前で検索..."
                       x-model="searchA" @input.debounce.350ms="searchPokemon('A')">
                <div x-show="resultsA.length > 0" class="border rounded mb-2" style="max-height:180px;overflow-y:auto">
                    <template x-for="p in resultsA" :key="p.id">
                        <div class="d-flex align-items-center p-1 px-2 gap-2"
                             style="cursor:pointer"
                             :class="selectedA?.id===p.id?'bg-primary text-white':''"
                             @click="selectPokemon('A', p)">
                            <img :src="p.sprite_url||''" style="width:32px;height:32px;object-fit:contain">
                            <span x-text="p.name_ja" class="fw-semibold"></span>
                            <small x-text="'#'+String(p.pokedex_number).padStart(4,'0')"
                                   :class="selectedA?.id===p.id?'text-white-50':'text-muted'"></small>
                        </div>
                    </template>
                </div>
                <template x-if="selectedA">
                    <div class="text-center pt-1">
                        <img :src="selectedA.sprite_url||''" style="height:96px;object-fit:contain">
                        <div class="fw-bold fs-5" x-text="selectedA.name_ja"></div>
                        <div>
                            <template x-for="t in (selectedA.types||[])" :key="t.type">
                                <span class="type-badge me-1" :class="'type-'+t.type" x-text="typeLabel(t.type)"></span>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- 中: VS -->
    <div class="col-md-2 d-flex align-items-center justify-content-center">
        <div class="text-center">
            <div class="display-4 text-muted fw-bold">VS</div>
            <button class="btn btn-sm btn-outline-secondary mt-2" @click="swapPokemon()"
                    :disabled="!selectedA || !selectedB" title="入れ替え">
                <i class="bi bi-arrow-left-right"></i>
            </button>
        </div>
    </div>

    <!-- 右: ポケモンB選択 -->
    <div class="col-md-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-danger text-white"><strong>ポケモン B</strong></div>
            <div class="card-body">
                <input type="text" class="form-control mb-2" placeholder="名前で検索..."
                       x-model="searchB" @input.debounce.350ms="searchPokemon('B')">
                <div x-show="resultsB.length > 0" class="border rounded mb-2" style="max-height:180px;overflow-y:auto">
                    <template x-for="p in resultsB" :key="p.id">
                        <div class="d-flex align-items-center p-1 px-2 gap-2"
                             style="cursor:pointer"
                             :class="selectedB?.id===p.id?'bg-danger text-white':''"
                             @click="selectPokemon('B', p)">
                            <img :src="p.sprite_url||''" style="width:32px;height:32px;object-fit:contain">
                            <span x-text="p.name_ja" class="fw-semibold"></span>
                            <small x-text="'#'+String(p.pokedex_number).padStart(4,'0')"
                                   :class="selectedB?.id===p.id?'text-white-50':'text-muted'"></small>
                        </div>
                    </template>
                </div>
                <template x-if="selectedB">
                    <div class="text-center pt-1">
                        <img :src="selectedB.sprite_url||''" style="height:96px;object-fit:contain">
                        <div class="fw-bold fs-5" x-text="selectedB.name_ja"></div>
                        <div>
                            <template x-for="t in (selectedB.types||[])" :key="t.type">
                                <span class="type-badge me-1" :class="'type-'+t.type" x-text="typeLabel(t.type)"></span>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- 比較テーブル -->
    <div class="col-12" x-show="selectedA && selectedB" x-cloak>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><strong>種族値比較</strong></div>
            <div class="card-body">
                <template x-for="stat in statNames" :key="stat.key">
                    <div class="row align-items-center mb-2">
                        <div class="col-md-1 text-muted text-end" style="font-size:.82rem" x-text="stat.label"></div>

                        <!-- A バー（右寄せ） -->
                        <div class="col-md-4">
                            <div class="d-flex align-items-center gap-2 flex-row-reverse">
                                <div class="fw-bold" style="width:32px;text-align:right;font-size:.9rem"
                                     :class="statA(stat.key) > statB(stat.key) ? 'text-primary' : statA(stat.key) < statB(stat.key) ? 'text-danger' : ''"
                                     x-text="statA(stat.key)"></div>
                                <div class="flex-grow-1 stat-bar">
                                    <div class="stat-bar-fill"
                                         :class="statA(stat.key) >= statB(stat.key) ? 'bg-primary' : 'bg-primary opacity-50'"
                                         :style="'width:'+Math.min(100,statA(stat.key)/255*100)+'%'"></div>
                                </div>
                            </div>
                        </div>

                        <!-- diff バッジ -->
                        <div class="col-md-2 text-center">
                            <template x-if="statA(stat.key) !== statB(stat.key)">
                                <span class="badge"
                                      :class="statA(stat.key) > statB(stat.key) ? 'bg-primary' : 'bg-danger'"
                                      x-text="(statA(stat.key) > statB(stat.key) ? 'A +' : 'B +') + Math.abs(statA(stat.key) - statB(stat.key))">
                                </span>
                            </template>
                            <template x-if="statA(stat.key) === statB(stat.key)">
                                <span class="badge bg-secondary">同値</span>
                            </template>
                        </div>

                        <!-- B バー -->
                        <div class="col-md-4">
                            <div class="d-flex align-items-center gap-2">
                                <div class="fw-bold" style="width:32px;font-size:.9rem"
                                     :class="statB(stat.key) > statA(stat.key) ? 'text-danger' : statB(stat.key) < statA(stat.key) ? 'text-muted' : ''"
                                     x-text="statB(stat.key)"></div>
                                <div class="flex-grow-1 stat-bar">
                                    <div class="stat-bar-fill"
                                         :class="statB(stat.key) >= statA(stat.key) ? 'bg-danger' : 'bg-danger opacity-50'"
                                         :style="'width:'+Math.min(100,statB(stat.key)/255*100)+'%'"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-1"></div>
                    </div>
                </template>

                <!-- 合計 -->
                <div class="row align-items-center mt-3 pt-3 border-top">
                    <div class="col-md-1 text-end text-muted" style="font-size:.82rem">合計</div>
                    <div class="col-md-4 text-end">
                        <span class="fw-bold fs-5"
                              :class="totalA > totalB ? 'text-primary' : totalA < totalB ? 'text-muted' : ''"
                              x-text="totalA"></span>
                    </div>
                    <div class="col-md-2 text-center">
                        <template x-if="totalA !== totalB">
                            <span class="badge fs-6"
                                  :class="totalA > totalB ? 'bg-primary' : 'bg-danger'"
                                  x-text="(totalA > totalB ? 'A +' : 'B +') + Math.abs(totalA - totalB)">
                            </span>
                        </template>
                        <template x-if="totalA === totalB">
                            <span class="badge bg-secondary fs-6">同値</span>
                        </template>
                    </div>
                    <div class="col-md-4">
                        <span class="fw-bold fs-5"
                              :class="totalB > totalA ? 'text-danger' : totalB < totalA ? 'text-muted' : ''"
                              x-text="totalB"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 特性比較 -->
    <div class="col-md-6" x-show="selectedA && selectedB" x-cloak>
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-primary text-white" style="font-size:.9rem">
                <strong x-text="selectedA?.name_ja + ' の特性'"></strong>
            </div>
            <div class="card-body py-2">
                <template x-for="a in (selectedA?.abilities||[])" :key="a.id">
                    <div class="mb-1">
                        <span class="fw-semibold" x-text="a.name_ja"></span>
                        <span x-show="a.pivot?.slot==3" class="badge bg-warning text-dark ms-1" style="font-size:.65rem">夢</span>
                        <div class="text-muted" style="font-size:.78rem" x-text="a.description||''"></div>
                    </div>
                </template>
            </div>
        </div>
    </div>
    <div class="col-md-6" x-show="selectedA && selectedB" x-cloak>
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-danger text-white" style="font-size:.9rem">
                <strong x-text="selectedB?.name_ja + ' の特性'"></strong>
            </div>
            <div class="card-body py-2">
                <template x-for="a in (selectedB?.abilities||[])" :key="a.id">
                    <div class="mb-1">
                        <span class="fw-semibold" x-text="a.name_ja"></span>
                        <span x-show="a.pivot?.slot==3" class="badge bg-warning text-dark ms-1" style="font-size:.65rem">夢</span>
                        <div class="text-muted" style="font-size:.78rem" x-text="a.description||''"></div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- 共通で覚えるわざ -->
    <div class="col-12" x-show="selectedA && selectedB && commonMoves.length > 0" x-cloak>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <strong>両方が覚えるわざ</strong>
                <span class="badge bg-secondary ms-2" x-text="commonMoves.length"></span>
            </div>
            <div class="card-body py-2">
                <div class="d-flex flex-wrap gap-2">
                    <template x-for="m in commonMoves" :key="m.id">
                        <span class="badge" :class="'bg-'+moveTypeColor(m.type)" x-text="m.name_ja"></span>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- 何も選択されていない -->
    <div class="col-12" x-show="!selectedA || !selectedB">
        <div class="text-center text-muted py-5 border rounded">
            <i class="bi bi-bar-chart-steps" style="font-size:2.5rem"></i>
            <div class="mt-2">AとBの両方のポケモンを選択すると種族値を比較できます</div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
const TYPE_LABELS = {
    normal:'ノーマル',fire:'ほのお',water:'みず',electric:'でんき',grass:'くさ',
    ice:'こおり',fighting:'かくとう',poison:'どく',ground:'じめん',flying:'ひこう',
    psychic:'エスパー',bug:'むし',rock:'いわ',ghost:'ゴースト',dragon:'ドラゴン',
    dark:'あく',steel:'はがね',fairy:'フェアリー'
};
const MOVE_TYPE_COLORS = {
    normal:'secondary',fire:'danger',water:'primary',electric:'warning',grass:'success',
    ice:'info',fighting:'danger',poison:'secondary',ground:'warning',flying:'info',
    psychic:'danger',bug:'success',rock:'secondary',ghost:'secondary',dragon:'primary',
    dark:'dark',steel:'secondary',fairy:'pink'
};

function pokemonCompare() {
    return {
        searchA: '', searchB: '',
        resultsA: [], resultsB: [],
        selectedA: null, selectedB: null,

        statNames: [
            {key:'base_hp',label:'HP'},{key:'base_attack',label:'攻撃'},{key:'base_defense',label:'防御'},
            {key:'base_sp_attack',label:'特攻'},{key:'base_sp_defense',label:'特防'},{key:'base_speed',label:'素早さ'},
        ],

        get totalA() { return this.statNames.reduce((s,st) => s + (this.selectedA?.[st.key]||0), 0); },
        get totalB() { return this.statNames.reduce((s,st) => s + (this.selectedB?.[st.key]||0), 0); },
        get commonMoves() {
            if (!this.selectedA || !this.selectedB) return [];
            const idsB = new Set((this.selectedB.moves||[]).map(m => m.id));
            return (this.selectedA.moves||[]).filter(m => idsB.has(m.id));
        },

        statA(key) { return this.selectedA?.[key] || 0; },
        statB(key) { return this.selectedB?.[key] || 0; },
        typeLabel(t) { return TYPE_LABELS[t] || t; },
        moveTypeColor(t) { return MOVE_TYPE_COLORS[t] || 'secondary'; },

        async searchPokemon(side) {
            const q = side === 'A' ? this.searchA : this.searchB;
            if (!q.trim()) {
                if (side === 'A') this.resultsA = []; else this.resultsB = [];
                return;
            }
            const res = await fetch(`/api/v1/pokemon?name=${encodeURIComponent(q)}&per_page=8`);
            const data = await res.json();
            if (side === 'A') this.resultsA = data.data || [];
            else              this.resultsB = data.data || [];
        },

        async selectPokemon(side, p) {
            const res = await fetch(`/api/v1/pokemon/${p.id}`);
            const full = await res.json();
            if (side === 'A') {
                this.selectedA = full;
                this.searchA = full.name_ja;
                this.resultsA = [];
            } else {
                this.selectedB = full;
                this.searchB = full.name_ja;
                this.resultsB = [];
            }
        },

        swapPokemon() {
            [this.selectedA, this.selectedB] = [this.selectedB, this.selectedA];
            [this.searchA, this.searchB]     = [this.searchB,   this.searchA];
        },
    };
}
</script>
@endpush
