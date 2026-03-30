@extends('layouts.app')
@section('title', 'ポケモン編集')
@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('custom-pokemon.index') }}">マイポケモン</a></li>
        <li class="breadcrumb-item"><a href="{{ route('custom-pokemon.show', $cp->id) }}">{{ $cp->display_name }}</a></li>
        <li class="breadcrumb-item active">編集</li>
    </ol>
</nav>
<h4 class="mb-3"><i class="bi bi-pencil"></i> ポケモン編集</h4>

@php
$initialData = [
    'id' => $cp->id,
    'pokemon' => ['id'=>$cp->pokemon->id,'name_ja'=>$cp->pokemon->name_ja,'sprite_url'=>$cp->pokemon->sprite_url,
                  'base_hp'=>$cp->pokemon->base_hp,'base_attack'=>$cp->pokemon->base_attack,
                  'base_defense'=>$cp->pokemon->base_defense,'base_sp_attack'=>$cp->pokemon->base_sp_attack,
                  'base_sp_defense'=>$cp->pokemon->base_sp_defense,'base_speed'=>$cp->pokemon->base_speed],
    'nature' => $cp->nature,
    'abilityId' => $cp->ability_id,
    'itemId' => $cp->item_id,
    'level' => $cp->level,
    'ivs' => ['hp'=>$cp->iv_hp,'attack'=>$cp->iv_attack,'defense'=>$cp->iv_defense,
              'sp_attack'=>$cp->iv_sp_attack,'sp_defense'=>$cp->iv_sp_defense,'speed'=>$cp->iv_speed],
    'evs' => ['hp'=>$cp->ev_hp,'attack'=>$cp->ev_attack,'defense'=>$cp->ev_defense,
              'sp_attack'=>$cp->ev_sp_attack,'sp_defense'=>$cp->ev_sp_defense,'speed'=>$cp->ev_speed],
    'moveIds' => $cp->moves->sortBy('pivot.slot')->pluck('id')->values()->toArray(),
    'nickname' => $cp->nickname,
    'memo' => $cp->memo,
];
@endphp

<div x-data="editForm({{ json_encode($initialData) }})" class="row g-3">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white"><strong>ポケモン</strong></div>
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <template x-if="selectedPokemon?.sprite_url">
                        <img :src="selectedPokemon.sprite_url" style="width:64px;height:64px;object-fit:contain" class="me-3">
                    </template>
                    <div>
                        <div class="fw-bold fs-5" x-text="selectedPokemon?.name_ja"></div>
                        <small class="text-muted">ポケモンの変更はできません（新規登録してください）</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white"><strong>性格・特性・持ち物</strong></div>
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
                        <select class="form-select" x-model="abilityId">
                            @foreach($cp->pokemon->abilities as $ability)
                                <option value="{{ $ability->id }}">{{ $ability->name_ja }}{{ $ability->pivot->slot==3?'（夢）':'' }}</option>
                            @endforeach
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

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white d-flex justify-content-between">
                <strong>個体値 (IV)</strong>
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

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white d-flex justify-content-between">
                <strong>努力値 (EV)</strong>
                <div>
                    <span :class="evTotal>510?'text-danger fw-bold':'text-muted'">合計: <span x-text="evTotal"></span>/510</span>
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

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong>技構成</strong>
                <small class="text-muted"><i class="bi bi-grip-vertical"></i> ドラッグで並べ替え可</small>
            </div>
            <div class="card-body">
                <div id="editMoveSlotContainer" class="row g-2">
                    <template x-for="(slot, idx) in moveSlots" :key="idx">
                        <div class="col-md-6" :data-idx="idx">
                            <div class="d-flex align-items-center gap-1">
                                <i class="bi bi-grip-vertical text-muted" style="cursor:grab;font-size:1.1rem"></i>
                                <label class="form-label mb-0 me-1" style="white-space:nowrap" x-text="'技'+(idx+1)"></label>
                                <select class="form-select form-select-sm flex-grow-1" x-model="moveSlots[idx]">
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

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white"><strong>ニックネーム・メモ</strong></div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="form-label">ニックネーム</label>
                        <input type="text" class="form-control" x-model="nickname" maxlength="50">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">メモ</label>
                        <textarea class="form-control" x-model="memo" rows="2"></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary" @click="submit()">
                <i class="bi bi-check-circle"></i> 更新する
            </button>
            <a href="{{ route('custom-pokemon.show', $cp->id) }}" class="btn btn-outline-secondary">キャンセル</a>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm sticky-top" style="top:70px">
            <div class="card-header bg-white"><strong>実数値プレビュー</strong></div>
            <div class="card-body">
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
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
function editForm(initial) {
    return {
        cpId: initial.id,
        selectedPokemon: initial.pokemon,
        nature: initial.nature,
        abilityId: initial.abilityId,
        itemId: initial.itemId || '',
        level: initial.level,
        ivs: {...initial.ivs},
        evs: {...initial.evs},
        moveSlots: [...initial.moveIds, '', '', '', ''].slice(0, 4),
        pokemonMoves: [],
        nickname: initial.nickname || '',
        memo: initial.memo || '',
        actualStats: {hp:0,attack:0,defense:0,sp_attack:0,sp_defense:0,speed:0},
        statNames: [
            {key:'hp',label:'HP'},{key:'attack',label:'攻撃'},{key:'defense',label:'防御'},
            {key:'sp_attack',label:'特攻'},{key:'sp_defense',label:'特防'},{key:'speed',label:'素早さ'},
        ],
        natureBoosts: {
            hardy:[],lonely:['attack','defense'],brave:['attack','speed'],adamant:['attack','sp_attack'],
            naughty:['attack','sp_defense'],bold:['defense','attack'],docile:[],relaxed:['defense','speed'],
            impish:['defense','sp_attack'],lax:['defense','sp_defense'],timid:['speed','attack'],
            hasty:['speed','defense'],serious:[],jolly:['speed','sp_attack'],naive:['speed','sp_defense'],
            modest:['sp_attack','attack'],mild:['sp_attack','defense'],quiet:['sp_attack','speed'],
            bashful:[],rash:['sp_attack','sp_defense'],calm:['sp_defense','attack'],gentle:['sp_defense','defense'],
            sassy:['sp_defense','speed'],careful:['sp_defense','sp_attack'],quirky:[],
        },
        get evTotal() { return Object.values(this.evs).reduce((a,b)=>a+(parseInt(b)||0),0); },

        async init() {
            const res = await fetch(`/api/v1/pokemon/${initial.pokemon.id}`);
            const d = await res.json();
            this.pokemonMoves = (d.moves||[]).sort((a,b)=>a.name_ja.localeCompare(b.name_ja,'ja'));
            this.updateStats();
            this.$nextTick(() => {
                const container = document.getElementById('editMoveSlotContainer');
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

        calcStat(base, iv, ev, lv, key) {
            if (key==='hp') return Math.floor((base*2+iv+Math.floor(ev/4))*lv/100)+lv+10;
            const raw = Math.floor(Math.floor((base*2+iv+Math.floor(ev/4))*lv/100)+5);
            const b = this.natureBoosts[this.nature]||[];
            return Math.floor(raw*(b[0]===key?1.1:b[1]===key?0.9:1.0));
        },

        updateStats() {
            const p = this.selectedPokemon; if(!p) return;
            const lv = parseInt(this.level)||50;
            const bases = {hp:p.base_hp,attack:p.base_attack,defense:p.base_defense,
                           sp_attack:p.base_sp_attack,sp_defense:p.base_sp_defense,speed:p.base_speed};
            for(const k of Object.keys(bases))
                this.actualStats[k]=this.calcStat(bases[k],parseInt(this.ivs[k])||0,parseInt(this.evs[k])||0,lv,k);
        },

        setAllIvs(v) { for(const k of Object.keys(this.ivs)) this.ivs[k]=v; this.updateStats(); },
        setAllEvs(v) { for(const k of Object.keys(this.evs)) this.evs[k]=v; this.updateStats(); },

        async submit() {
            const payload = {
                pokemon_id: this.selectedPokemon.id,
                ability_id: parseInt(this.abilityId),
                item_id: this.itemId ? parseInt(this.itemId) : null,
                nature: this.nature, level: parseInt(this.level),
                ivs: {...this.ivs}, evs: {...this.evs},
                move_ids: this.moveSlots.filter(m=>m!==''),
                nickname: this.nickname||null, memo: this.memo||null,
            };
            const res = await fetch(`/api/v1/custom-pokemon/${this.cpId}`, {
                method: 'PUT',
                headers: {'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},
                body: JSON.stringify(payload),
            });
            if (res.ok) { window.location.href = `/custom-pokemon/${this.cpId}`; }
            else { const e=await res.json(); alert('エラー: '+JSON.stringify(e.errors||e.message)); }
        },
    };
}
</script>
@endpush
