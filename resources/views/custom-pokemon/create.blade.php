@extends('layouts.app')
@section('title', 'ポケモン登録')
@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('custom-pokemon.index') }}">マイポケモン</a></li>
        <li class="breadcrumb-item active">新規登録</li>
    </ol>
</nav>
<h4 class="mb-3"><i class="bi bi-plus-circle"></i> ポケモン登録</h4>
<div x-data="customPokemonForm({{ old('pokemon_id', request('pokemon_id', 'null')) }})" class="row g-3">
    <div class="col-lg-8">
        <!-- 1. ポケモン選択 -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white"><strong>1. ポケモン選択</strong></div>
            <div class="card-body">
                <input type="text" class="form-control mb-2" placeholder="ポケモン名で検索..."
                       x-model="pokemonSearch" @input.debounce.400ms="searchPokemon()">
                <div x-show="pokemonResults.length > 0" class="border rounded p-2 mb-2" style="max-height:200px;overflow-y:auto">
                    <template x-for="p in pokemonResults" :key="p.id">
                        <div class="d-flex align-items-center p-1 rounded"
                             :class="selectedPokemon?.id===p.id?'bg-primary text-white':''"
                             @click="selectPokemon(p)" style="cursor:pointer">
                            <img :src="p.sprite_url||''" style="width:36px;height:36px;object-fit:contain" class="me-2">
                            <span x-text="p.name_ja" class="fw-semibold"></span>
                            <small class="ms-2" x-text="'#'+String(p.pokedex_number).padStart(4,'0')"></small>
                        </div>
                    </template>
                </div>
                <div x-show="selectedPokemon" class="alert alert-success py-2 mb-0">
                    選択中: <strong x-text="selectedPokemon?.name_ja"></strong>
                </div>
            </div>
        </div>

        <!-- 2. 性格・特性・持ち物 -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white"><strong>2. 性格・特性・持ち物</strong></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">性格</label>
                        <select class="form-select" x-model="nature" @change="updateStats()">
                            @foreach($natures as $n)
                                <option value="{{ $n->value }}">{{ $n->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">特性</label>
                        <select class="form-select" x-model="abilityId" :disabled="!selectedPokemon">
                            <option value="">-- 選択 --</option>
                            <template x-for="a in pokemonAbilities" :key="a.id">
                                <option :value="a.id" x-text="a.name_ja+(a.pivot?.slot==3?'（夢）':'')"></option>
                            </template>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">持ち物</label>
                        <select class="form-select" x-model="itemId">
                            <option value="">なし</option>
                            @foreach($items as $item)
                                <option value="{{ $item->id }}">{{ $item->name_ja }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">レベル</label>
                        <input type="number" class="form-control" x-model.number="level" min="1" max="100" @input="updateStats()">
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. 個体値 -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong>3. 個体値 (IV)</strong>
                <button type="button" class="btn btn-sm btn-outline-secondary" @click="setAllIvs(31)">全31</button>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <template x-for="stat in statNames" :key="stat.key">
                        <div class="col-md-4">
                            <label class="form-label mb-0" x-text="stat.label+' IV'"></label>
                            <div class="d-flex align-items-center gap-2">
                                <input type="range" class="form-range flex-grow-1" min="0" max="31"
                                       :value="ivs[stat.key]"
                                       @input="ivs[stat.key]=parseInt($event.target.value);updateStats()">
                                <input type="number" class="form-control" style="width:65px"
                                       x-model.number="ivs[stat.key]" min="0" max="31" @input="updateStats()">
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- 4. 努力値 -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong>4. 努力値 (EV)</strong>
                <div>
                    <span :class="evTotal>510?'text-danger fw-bold':'text-muted'">
                        合計: <span x-text="evTotal"></span>/510
                    </span>
                    <button type="button" class="btn btn-sm btn-outline-secondary ms-2" @click="setAllEvs(0)">リセット</button>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <template x-for="stat in statNames" :key="stat.key">
                        <div class="col-md-4">
                            <label class="form-label mb-0" x-text="stat.label+' EV'"></label>
                            <div class="d-flex align-items-center gap-2">
                                <input type="range" class="form-range flex-grow-1" min="0" max="252" step="4"
                                       :value="evs[stat.key]"
                                       @input="evs[stat.key]=parseInt($event.target.value);updateStats()">
                                <input type="number" class="form-control" style="width:65px"
                                       x-model.number="evs[stat.key]" min="0" max="252" step="4" @input="updateStats()">
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- 5. 技構成 -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong>5. 技構成</strong>
                <small class="text-muted"><i class="bi bi-grip-vertical"></i> ドラッグで並べ替え可</small>
            </div>
            <div class="card-body">
                <div id="moveSlotContainer" class="row g-2">
                    <template x-for="(slot, idx) in moveSlots" :key="idx">
                        <div class="col-md-6" :data-idx="idx">
                            <div class="d-flex align-items-center gap-1">
                                <i class="bi bi-grip-vertical text-muted" style="cursor:grab;font-size:1.1rem"></i>
                                <label class="form-label mb-0 me-1" style="white-space:nowrap" x-text="'技'+(idx+1)"></label>
                                <select class="form-select form-select-sm flex-grow-1" x-model="moveSlots[idx]" :disabled="!selectedPokemon">
                                    <option value="">-- なし --</option>
                                    <template x-for="m in pokemonMoves" :key="m.id">
                                        <option :value="m.id" x-text="m.name_ja"></option>
                                    </template>
                                </select>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- 6. メモ -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white"><strong>6. ニックネーム・メモ</strong></div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="form-label">ニックネーム</label>
                        <input type="text" class="form-control" x-model="nickname" maxlength="50" placeholder="任意">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">メモ</label>
                        <textarea class="form-control" x-model="memo" rows="2" placeholder="調整意図など"></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="button" class="btn btn-success" @click="submit()" :disabled="!selectedPokemon||!abilityId">
                <i class="bi bi-check-circle"></i> 登録する
            </button>
            <a href="{{ route('custom-pokemon.index') }}" class="btn btn-outline-secondary">キャンセル</a>
        </div>
    </div>

    <!-- 実数値プレビュー -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm sticky-top" style="top:70px">
            <div class="card-header bg-white"><strong>実数値プレビュー</strong></div>
            <div class="card-body">
                <template x-if="!selectedPokemon">
                    <div class="text-muted text-center py-3">ポケモンを選択してください</div>
                </template>
                <template x-if="selectedPokemon">
                    <div>
                        <template x-for="stat in statNames" :key="stat.key">
                            <div class="d-flex align-items-center mb-2">
                                <div style="width:50px;font-size:.8rem" class="text-muted" x-text="stat.label"></div>
                                <div class="fw-bold me-2" style="width:35px;font-size:.9rem" x-text="actualStats[stat.key]||0"></div>
                                <div class="flex-grow-1 stat-bar">
                                    <div class="stat-bar-fill bg-primary"
                                         :style="'width:'+Math.min(100,(actualStats[stat.key]||0)/500*100)+'%'"></div>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
function customPokemonForm(initialPokemonId) {
    return {
        pokemonSearch: '', pokemonResults: [], selectedPokemon: null,
        pokemonAbilities: [], pokemonMoves: [],
        nature: 'hardy', abilityId: '', itemId: '', level: 50, nickname: '', memo: '',
        ivs: {hp:31,attack:31,defense:31,sp_attack:31,sp_defense:31,speed:31},
        evs: {hp:0,attack:0,defense:0,sp_attack:0,sp_defense:0,speed:0},
        moveSlots: ['','','',''],
        actualStats: {hp:0,attack:0,defense:0,sp_attack:0,sp_defense:0,speed:0},
        statNames: [
            {key:'hp',label:'HP'},{key:'attack',label:'攻撃'},{key:'defense',label:'防御'},
            {key:'sp_attack',label:'特攻'},{key:'sp_defense',label:'特防'},{key:'speed',label:'素早さ'},
        ],
        get evTotal() { return Object.values(this.evs).reduce((a,b)=>a+(parseInt(b)||0),0); },

        // 性格補正テーブル [上昇, 下降]
        natureBoosts: {
            hardy:[],lonely:['attack','defense'],brave:['attack','speed'],adamant:['attack','sp_attack'],
            naughty:['attack','sp_defense'],bold:['defense','attack'],docile:[],relaxed:['defense','speed'],
            impish:['defense','sp_attack'],lax:['defense','sp_defense'],timid:['speed','attack'],
            hasty:['speed','defense'],serious:[],jolly:['speed','sp_attack'],naive:['speed','sp_defense'],
            modest:['sp_attack','attack'],mild:['sp_attack','defense'],quiet:['sp_attack','speed'],
            bashful:[],rash:['sp_attack','sp_defense'],calm:['sp_defense','attack'],gentle:['sp_defense','defense'],
            sassy:['sp_defense','speed'],careful:['sp_defense','sp_attack'],quirky:[],
        },

        async init() {
            if (initialPokemonId) {
                const res = await fetch(`/api/v1/pokemon/${initialPokemonId}`);
                const p = await res.json();
                this.selectPokemon(p);
            }
            this.$nextTick(() => {
                const container = document.getElementById('moveSlotContainer');
                if (container && window.Sortable) {
                    Sortable.create(container, {
                        handle: '.bi-grip-vertical',
                        animation: 150,
                        onEnd: () => {
                            const newOrder = [];
                            container.querySelectorAll('select.form-select').forEach(sel => {
                                newOrder.push(sel.value);
                            });
                            this.moveSlots = newOrder;
                        },
                    });
                }
            });
        },

        async searchPokemon() {
            if (!this.pokemonSearch.trim()) { this.pokemonResults = []; return; }
            const res = await fetch(`/api/v1/pokemon?name=${encodeURIComponent(this.pokemonSearch)}&per_page=10`);
            const data = await res.json();
            this.pokemonResults = data.data || [];
        },

        async selectPokemon(p) {
            this.selectedPokemon = p;
            this.pokemonSearch = p.name_ja;
            this.pokemonResults = [];
            const res = await fetch(`/api/v1/pokemon/${p.id}`);
            const d = await res.json();
            this.pokemonAbilities = d.abilities || [];
            this.pokemonMoves = (d.moves || []).sort((a,b) => a.name_ja.localeCompare(b.name_ja, 'ja'));
            if (this.pokemonAbilities.length > 0) this.abilityId = this.pokemonAbilities[0].id;
            this.updateStats();
        },

        calcStat(base, iv, ev, lv, statKey) {
            if (statKey === 'hp')
                return Math.floor((base*2+iv+Math.floor(ev/4))*lv/100)+lv+10;
            const raw = Math.floor(Math.floor((base*2+iv+Math.floor(ev/4))*lv/100)+5);
            const boosts = this.natureBoosts[this.nature] || [];
            const mod = boosts[0]===statKey ? 1.1 : boosts[1]===statKey ? 0.9 : 1.0;
            return Math.floor(raw*mod);
        },

        updateStats() {
            if (!this.selectedPokemon) return;
            const p = this.selectedPokemon;
            const lv = parseInt(this.level)||50;
            const bases = {hp:p.base_hp,attack:p.base_attack,defense:p.base_defense,
                           sp_attack:p.base_sp_attack,sp_defense:p.base_sp_defense,speed:p.base_speed};
            for (const key of Object.keys(bases)) {
                this.actualStats[key] = this.calcStat(bases[key], parseInt(this.ivs[key])||0, parseInt(this.evs[key])||0, lv, key);
            }
        },

        setAllIvs(v) { for(const k of Object.keys(this.ivs)) this.ivs[k]=v; this.updateStats(); },
        setAllEvs(v) { for(const k of Object.keys(this.evs)) this.evs[k]=v; this.updateStats(); },

        async submit() {
            if (!this.selectedPokemon || !this.abilityId) return;
            const payload = {
                pokemon_id: this.selectedPokemon.id,
                ability_id: parseInt(this.abilityId),
                item_id: this.itemId ? parseInt(this.itemId) : null,
                nature: this.nature, level: parseInt(this.level),
                ivs: {...this.ivs}, evs: {...this.evs},
                move_ids: this.moveSlots.filter(m => m !== ''),
                nickname: this.nickname||null, memo: this.memo||null,
            };
            const res = await fetch('/api/v1/custom-pokemon', {
                method: 'POST',
                headers: {'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},
                body: JSON.stringify(payload),
            });
            if (res.ok) { window.location.href = '/custom-pokemon'; }
            else { const e=await res.json(); alert('エラー: '+JSON.stringify(e.errors||e.message)); }
        },
    };
}
</script>
@endpush
