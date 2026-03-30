@extends('layouts.app')
@section('title', 'ダメージ計算')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-calculator"></i> ダメージ計算</h4>
    <a href="{{ route('damage-calc.formula') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-info-circle"></i> 計算式の解説
    </a>
</div>
<div x-data="damageCalc({{ $attackerPokemon?->id ?? 'null' }}, {{ $defenderPokemon?->id ?? 'null' }})" x-init="init()">
    <div class="row g-3">
        <!-- 攻撃側 -->
        <div class="col-md-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                    <strong><i class="bi bi-lightning"></i> 攻撃側</strong>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-sm" :class="attackerMode==='my'?'btn-light':'btn-outline-light'" @click="attackerMode='my'">マイポケモン</button>
                        <button class="btn btn-sm" :class="attackerMode==='adhoc'?'btn-light':'btn-outline-light'" @click="attackerMode='adhoc'">直接入力</button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- マイポケモンモード -->
                    <template x-if="attackerMode==='my'">
                        <div>
                            <label class="form-label">マイポケモンから選択</label>
                            <select class="form-select mb-2" x-model="attackerId" @change="loadAttacker()">
                                <option value="">-- 選択 --</option>
                                @foreach($myPokemonList as $cp)
                                    <option value="{{ $cp->id }}">{{ $cp->display_name }} ({{ $cp->pokemon->name_ja }})</option>
                                @endforeach
                            </select>
                            <template x-if="attackerInfo">
                                <div class="p-2 bg-light rounded mb-2">
                                    <div class="fw-semibold" x-text="attackerInfo.display_name"></div>
                                    <small class="text-muted">HP実数値: <span x-text="attackerInfo.actual_stats?.hp"></span></small>
                                </div>
                            </template>
                        </div>
                    </template>
                    <!-- アドホックモード -->
                    <template x-if="attackerMode==='adhoc'">
                        <div>
                            <div class="mb-1">
                                <input type="text" class="form-control form-control-sm" x-model="adhocAttacker.search"
                                       @input.debounce.400ms="searchPokemon('attacker')"
                                       placeholder="ポケモン名で検索...">
                                <select class="form-select form-select-sm mt-1" x-model="adhocAttacker.pokemon_id"
                                        @change="onAdhocPokemonChange('attacker')">
                                    <option value="">-- 選択 --</option>
                                    <template x-for="p in adhocAttacker.results" :key="p.id">
                                        <option :value="p.id" x-text="p.name_ja"></option>
                                    </template>
                                </select>
                            </div>
                            <div class="row g-1 mb-1">
                                <div class="col-4">
                                    <label style="font-size:.75rem">レベル</label>
                                    <input type="number" class="form-control form-control-sm" x-model.number="adhocAttacker.level" min="1" max="100" @change="calcAdhocStats('attacker')">
                                </div>
                                <div class="col-8">
                                    <label style="font-size:.75rem">性格</label>
                                    <select class="form-select form-select-sm" x-model="adhocAttacker.nature" @change="calcAdhocStats('attacker')">
                                        @foreach($natures as $nature)
                                            <option value="{{ $nature->value }}">{{ $nature->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row g-1 mb-1">
                                @foreach(['attack'=>'攻撃EV','sp_attack'=>'特攻EV'] as $stat => $label)
                                <div class="col-6">
                                    <label style="font-size:.75rem">{{ $label }}</label>
                                    <input type="number" class="form-control form-control-sm" x-model.number="adhocAttacker.evs.{{ $stat }}" min="0" max="252" step="4" @change="calcAdhocStats('attacker')">
                                </div>
                                @endforeach
                            </div>
                            <template x-if="adhocAttacker.stats">
                                <div class="p-2 bg-light rounded" style="font-size:.8rem">
                                    <span>攻撃: <strong x-text="adhocAttacker.stats.attack"></strong></span>
                                    <span class="ms-2">特攻: <strong x-text="adhocAttacker.stats.sp_attack"></strong></span>
                                    <span class="ms-2">HP: <strong x-text="adhocAttacker.stats.hp"></strong></span>
                                </div>
                            </template>
                        </div>
                    </template>
                    <!-- ランク補正（共通） -->
                    <div class="row g-1 mt-2">
                        <div class="col-6">
                            <label class="form-label mb-0" style="font-size:.8rem">攻撃ランク</label>
                            <select class="form-select form-select-sm" x-model.number="attackerRank.attack">
                                <template x-for="r in rankOptions" :key="r">
                                    <option :value="r" x-text="(r>0?'+':'')+r"></option>
                                </template>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label mb-0" style="font-size:.8rem">特攻ランク</label>
                            <select class="form-select form-select-sm" x-model.number="attackerRank.sp_attack">
                                <template x-for="r in rankOptions" :key="r">
                                    <option :value="r" x-text="(r>0?'+':'')+r"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- わざ・環境 -->
        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-warning text-dark text-center"><strong>わざ・環境</strong></div>
                <div class="card-body">
                    <label class="form-label mb-1" style="font-size:.8rem">使用わざ</label>
                    <!-- マイポケモンの技をクイック選択 -->
                    <div x-show="attackerMode==='my' && attackerMoves.length > 0" class="mb-1">
                        <select class="form-select form-select-sm" x-model="moveId" @change="onMoveChange()">
                            <option value="">-- マイ技から選択 --</option>
                            <template x-for="m in attackerMoves" :key="m.id">
                                <option :value="m.id" x-text="m.name_ja"></option>
                            </template>
                        </select>
                        <div class="text-center my-1" style="font-size:.75rem;color:#aaa">または</div>
                    </div>
                    <!-- 常に表示: わざ名検索 -->
                    <div>
                        <input type="text" class="form-control form-control-sm mb-1" x-model="moveSearch"
                               @input.debounce.400ms="searchMoves()"
                               placeholder="わざ名で検索...">
                        <select class="form-select form-select-sm mb-1" x-model="moveId" @change="onMoveChange()"
                                x-show="moveResults.length > 0">
                            <option value="">-- 検索結果から選択 --</option>
                            <template x-for="m in moveResults" :key="m.id">
                                <option :value="m.id" x-text="m.name_ja"></option>
                            </template>
                        </select>
                    </div>
                    <!-- 選択中の技情報 -->
                    <div x-show="selectedMove" class="text-center mb-1 p-1 bg-warning bg-opacity-10 rounded" style="font-size:.8rem">
                        <strong x-text="selectedMove?.name_ja"></strong>
                        <span class="ms-1 text-muted">威力: <strong x-text="selectedMove?.power||'-'"></strong></span>
                    </div>
                    <hr class="my-2">
                    <label class="form-label mb-1" style="font-size:.8rem">天気</label>
                    <select class="form-select form-select-sm mb-2" x-model="weather">
                        <option value="none">なし</option>
                        <option value="sunny">晴れ</option>
                        <option value="rainy">雨</option>
                        <option value="sandstorm">砂嵐</option>
                        <option value="snow">雪</option>
                    </select>
                    <label class="form-label mb-1" style="font-size:.8rem">フィールド</label>
                    <select class="form-select form-select-sm mb-2" x-model="terrain">
                        <option value="none">なし</option>
                        <option value="grassy">グラスフィールド</option>
                        <option value="electric">エレキフィールド</option>
                        <option value="psychic">サイコフィールド</option>
                        <option value="misty">ミストフィールド</option>
                    </select>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" x-model="isCritical" id="critCheck">
                        <label class="form-check-label" for="critCheck" style="font-size:.85rem">急所</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" x-model="burned" id="burnCheck">
                        <label class="form-check-label" for="burnCheck" style="font-size:.85rem">やけど</label>
                    </div>
                </div>
            </div>
        </div>

        <!-- 防御側 -->
        <div class="col-md-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <strong><i class="bi bi-shield"></i> 防御側</strong>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-sm" :class="defenderMode==='my'?'btn-light':'btn-outline-light'" @click="defenderMode='my'">マイポケモン</button>
                        <button class="btn btn-sm" :class="defenderMode==='adhoc'?'btn-light':'btn-outline-light'" @click="defenderMode='adhoc'">直接入力</button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- マイポケモンモード -->
                    <template x-if="defenderMode==='my'">
                        <div>
                            <label class="form-label">マイポケモンから選択</label>
                            <select class="form-select mb-2" x-model="defenderId" @change="loadDefender()">
                                <option value="">-- 選択 --</option>
                                @foreach($myPokemonList as $cp)
                                    <option value="{{ $cp->id }}">{{ $cp->display_name }} ({{ $cp->pokemon->name_ja }})</option>
                                @endforeach
                            </select>
                            <template x-if="defenderInfo">
                                <div class="p-2 bg-light rounded mb-2">
                                    <div class="fw-semibold" x-text="defenderInfo.display_name"></div>
                                    <small class="text-muted">HP実数値: <span x-text="defenderInfo.actual_stats?.hp"></span></small>
                                </div>
                            </template>
                        </div>
                    </template>
                    <!-- アドホックモード -->
                    <template x-if="defenderMode==='adhoc'">
                        <div>
                            <div class="mb-1">
                                <input type="text" class="form-control form-control-sm" x-model="adhocDefender.search"
                                       @input.debounce.400ms="searchPokemon('defender')"
                                       placeholder="ポケモン名で検索...">
                                <select class="form-select form-select-sm mt-1" x-model="adhocDefender.pokemon_id"
                                        @change="onAdhocPokemonChange('defender')">
                                    <option value="">-- 選択 --</option>
                                    <template x-for="p in adhocDefender.results" :key="p.id">
                                        <option :value="p.id" x-text="p.name_ja"></option>
                                    </template>
                                </select>
                            </div>
                            <div class="row g-1 mb-1">
                                <div class="col-4">
                                    <label style="font-size:.75rem">レベル</label>
                                    <input type="number" class="form-control form-control-sm" x-model.number="adhocDefender.level" min="1" max="100" @change="calcAdhocStats('defender')">
                                </div>
                                <div class="col-8">
                                    <label style="font-size:.75rem">性格</label>
                                    <select class="form-select form-select-sm" x-model="adhocDefender.nature" @change="calcAdhocStats('defender')">
                                        @foreach($natures as $nature)
                                            <option value="{{ $nature->value }}">{{ $nature->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row g-1 mb-1">
                                @foreach(['hp'=>'HP EV','defense'=>'防御EV','sp_defense'=>'特防EV'] as $stat => $label)
                                <div class="col-4">
                                    <label style="font-size:.75rem">{{ $label }}</label>
                                    <input type="number" class="form-control form-control-sm" x-model.number="adhocDefender.evs.{{ $stat }}" min="0" max="252" step="4" @change="calcAdhocStats('defender')">
                                </div>
                                @endforeach
                            </div>
                            <template x-if="adhocDefender.stats">
                                <div class="p-2 bg-light rounded" style="font-size:.8rem">
                                    <span>HP: <strong x-text="adhocDefender.stats.hp"></strong></span>
                                    <span class="ms-2">防御: <strong x-text="adhocDefender.stats.defense"></strong></span>
                                    <span class="ms-2">特防: <strong x-text="adhocDefender.stats.sp_defense"></strong></span>
                                </div>
                            </template>
                        </div>
                    </template>
                    <!-- ランク補正（共通） -->
                    <div class="row g-1 mt-2">
                        <div class="col-6">
                            <label class="form-label mb-0" style="font-size:.8rem">防御ランク</label>
                            <select class="form-select form-select-sm" x-model.number="defenderRank.defense">
                                <template x-for="r in rankOptions" :key="r">
                                    <option :value="r" x-text="(r>0?'+':'')+r"></option>
                                </template>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label mb-0" style="font-size:.8rem">特防ランク</label>
                            <select class="form-select form-select-sm" x-model.number="defenderRank.sp_defense">
                                <template x-for="r in rankOptions" :key="r">
                                    <option :value="r" x-text="(r>0?'+':'')+r"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 計算ボタン -->
        <div class="col-12 text-center">
            <button class="btn btn-lg btn-warning fw-bold px-5"
                    @click="calculate()"
                    :disabled="!canCalculate">
                <i class="bi bi-calculator"></i> ダメージ計算
            </button>
        </div>

        <!-- 結果 -->
        <div class="col-12" x-show="result">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <strong>計算結果</strong>
                    <button class="btn btn-sm btn-outline-success" @click="saveToMemo()" title="メモに追加">
                        <i class="bi bi-journal-plus"></i> メモに追加
                    </button>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-3">
                        <div class="col-md-3">
                            <div class="text-muted" style="font-size:.8rem">ダメージ</div>
                            <div class="fw-bold fs-4">
                                <span x-text="result?.damage_min"></span>~<span x-text="result?.damage_max"></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-muted" style="font-size:.8rem">割合</div>
                            <div class="fw-bold fs-4">
                                <span x-text="result?.damage_percent_min"></span>%~<span x-text="result?.damage_percent_max"></span>%
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-muted" style="font-size:.8rem">タイプ相性</div>
                            <div class="fw-bold fs-4" x-text="result?.type_effectiveness+'x'"></div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-muted" style="font-size:.8rem">確定</div>
                            <div class="fw-bold fs-4">
                                <span x-show="result?.one_shot" class="text-danger">確定1発</span>
                                <span x-show="!result?.one_shot && result?.two_shot" class="text-warning">確定2発</span>
                                <span x-show="!result?.one_shot && !result?.two_shot" class="text-muted">3発以上</span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="text-muted mb-1" style="font-size:.8rem">乱数16本</div>
                        <div class="d-flex gap-1 flex-wrap">
                            <template x-for="(roll, i) in (result?.rolls||[])" :key="i">
                                <div class="text-center px-2 py-1 rounded"
                                     :class="roll >= defenderHp ? 'bg-danger text-white' : 'bg-light border'"
                                     style="min-width:42px;font-size:.8rem" x-text="roll"></div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- メモリスト -->
        <div class="col-12" x-show="memos.length > 0">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <strong><i class="bi bi-journal-text"></i> 計算メモ</strong>
                    <button class="btn btn-sm btn-outline-danger" @click="memos=[]">
                        <i class="bi bi-trash"></i> 全削除
                    </button>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-hover mb-0" style="font-size:.82rem">
                        <thead class="table-light">
                            <tr><th>説明</th><th>ダメージ</th><th>割合</th><th>相性</th><th>確定</th><th style="width:36px"></th></tr>
                        </thead>
                        <tbody>
                            <template x-for="(memo, idx) in memos" :key="idx">
                                <tr>
                                    <td>
                                        <input type="text" class="form-control form-control-sm border-0 p-0 bg-transparent"
                                               x-model="memo.label" style="min-width:150px">
                                    </td>
                                    <td x-text="memo.damage_min+'~'+memo.damage_max"></td>
                                    <td x-text="memo.damage_percent_min+'%~'+memo.damage_percent_max+'%'"></td>
                                    <td x-text="memo.type_effectiveness+'x'"></td>
                                    <td>
                                        <span x-show="memo.one_shot" class="badge bg-danger">確定1発</span>
                                        <span x-show="!memo.one_shot && memo.two_shot" class="badge bg-warning text-dark">確定2発</span>
                                        <span x-show="!memo.one_shot && !memo.two_shot" class="text-muted">3発以上</span>
                                    </td>
                                    <td>
                                        <button class="btn btn-xs btn-outline-danger p-0 px-1" @click="memos.splice(idx,1)">✕</button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
function damageCalc(initAttackerId, initDefenderId) {
    return {
        // モード: 'my' or 'adhoc'
        attackerMode: initAttackerId ? 'my' : 'my',
        defenderMode: 'my',

        // マイポケモンモード
        attackerId: initAttackerId || '',
        defenderId: initDefenderId || '',
        attackerInfo: null, defenderInfo: null,
        attackerMoves: [], moveId: '', selectedMove: null,
        moveSearch: '', moveResults: [],

        // アドホックモード
        adhocAttacker: {search:'', results:[], pokemon_id:'', level:50, nature:'hardy',
                        evs:{hp:0,attack:0,defense:0,sp_attack:0,sp_defense:0,speed:0},
                        ivs:{hp:31,attack:31,defense:31,sp_attack:31,sp_defense:31,speed:31},
                        stats:null, basePokemon:null},
        adhocDefender: {search:'', results:[], pokemon_id:'', level:50, nature:'hardy',
                        evs:{hp:0,attack:0,defense:0,sp_attack:0,sp_defense:0,speed:0},
                        ivs:{hp:31,attack:31,defense:31,sp_attack:31,sp_defense:31,speed:31},
                        stats:null, basePokemon:null},

        weather: 'none', terrain: 'none', isCritical: false, burned: false,
        attackerRank: {attack:0, sp_attack:0},
        defenderRank: {defense:0, sp_defense:0},
        result: null,
        memos: [],
        rankOptions: [-6,-5,-4,-3,-2,-1,0,1,2,3,4,5,6],

        get defenderHp() {
            if (this.defenderMode === 'adhoc') return this.adhocDefender.stats?.hp || Infinity;
            return this.defenderInfo?.actual_stats?.hp || Infinity;
        },

        get canCalculate() {
            const atkOk = this.attackerMode === 'my' ? !!this.attackerId : !!this.adhocAttacker.pokemon_id;
            const defOk = this.defenderMode === 'my' ? !!this.defenderId : !!this.adhocDefender.pokemon_id;
            return atkOk && defOk && !!this.moveId;
        },

        async init() {
            if (this.attackerId) await this.loadAttacker();
            if (this.defenderId) await this.loadDefender();
        },

        async loadAttacker() {
            if (!this.attackerId) { this.attackerInfo = null; this.attackerMoves = []; return; }
            const res = await fetch(`/api/v1/custom-pokemon/${this.attackerId}`);
            this.attackerInfo = await res.json();
            this.attackerMoves = this.attackerInfo.moves || [];
        },

        async loadDefender() {
            if (!this.defenderId) { this.defenderInfo = null; return; }
            const res = await fetch(`/api/v1/custom-pokemon/${this.defenderId}`);
            this.defenderInfo = await res.json();
        },

        async searchMoves() {
            if (!this.moveSearch.trim()) return;
            const res = await fetch(`/api/v1/moves?name=${encodeURIComponent(this.moveSearch)}&per_page=20`);
            const data = await res.json();
            this.moveResults = data.data || [];
        },

        async onMoveChange() {
            if (!this.moveId) { this.selectedMove = null; return; }
            // attackerMovesから先に探す（API呼び出し節約）
            const cached = [...this.attackerMoves, ...this.moveResults].find(m => m.id == this.moveId);
            if (cached) { this.selectedMove = cached; return; }
            const res = await fetch(`/api/v1/moves/${this.moveId}`);
            this.selectedMove = await res.json();
        },

        async searchPokemon(side) {
            const query = side === 'attacker' ? this.adhocAttacker.search : this.adhocDefender.search;
            if (!query.trim()) return;
            const res = await fetch(`/api/v1/pokemon?name=${encodeURIComponent(query)}&per_page=20`);
            const data = await res.json();
            if (side === 'attacker') this.adhocAttacker.results = data.data || [];
            else this.adhocDefender.results = data.data || [];
        },

        async onAdhocPokemonChange(side) {
            const obj = side === 'attacker' ? this.adhocAttacker : this.adhocDefender;
            if (!obj.pokemon_id) { obj.basePokemon = null; obj.stats = null; return; }
            const res = await fetch(`/api/v1/pokemon/${obj.pokemon_id}`);
            obj.basePokemon = await res.json();
            this.calcAdhocStats(side);
        },

        calcAdhocStats(side) {
            const obj = side === 'attacker' ? this.adhocAttacker : this.adhocDefender;
            if (!obj.basePokemon) return;
            const p = obj.basePokemon;
            const lv = obj.level || 50;
            const evs = obj.evs;
            const ivs = obj.ivs;
            const natureBoosts = @json(collect(\App\Enums\Nature::cases())->mapWithKeys(fn($n) => [$n->value, ['boost' => $n->boostedStat(), 'reduce' => $n->reducedStat()]]));
            const nb = natureBoosts[obj.nature] || {};
            const nm = (stat) => nb.boost === stat ? 1.1 : nb.reduce === stat ? 0.9 : 1.0;
            const calcHp = (base, iv, ev) => Math.floor((base*2+iv+Math.floor(ev/4))*lv/100)+lv+10;
            const calcStat = (base, iv, ev, stat) => Math.floor((Math.floor((base*2+iv+Math.floor(ev/4))*lv/100)+5)*nm(stat));
            obj.stats = {
                hp:         calcHp(p.base_hp,         ivs.hp||31,         evs.hp||0),
                attack:     calcStat(p.base_attack,     ivs.attack||31,     evs.attack||0,     'attack'),
                defense:    calcStat(p.base_defense,    ivs.defense||31,    evs.defense||0,    'defense'),
                sp_attack:  calcStat(p.base_sp_attack,  ivs.sp_attack||31,  evs.sp_attack||0,  'sp_attack'),
                sp_defense: calcStat(p.base_sp_defense, ivs.sp_defense||31, evs.sp_defense||0, 'sp_defense'),
                speed:      calcStat(p.base_speed,      ivs.speed||31,      evs.speed||0,      'speed'),
            };
        },

        async calculate() {
            const modifiers = [];
            if (this.burned) modifiers.push('burned');

            let payload, url;
            if (this.attackerMode === 'adhoc' || this.defenderMode === 'adhoc') {
                // アドホックエンドポイント
                url = '/api/v1/damage-calc/adhoc';
                const buildSide = (mode, myId, adhoc) => {
                    if (mode === 'my') {
                        // マイポケモン側はサーバーで計算するためIDのみ送る
                        return {pokemon_id: parseInt(myId), _use_custom: true, custom_id: parseInt(myId)};
                    }
                    return {
                        pokemon_id: parseInt(adhoc.pokemon_id),
                        level: adhoc.level,
                        nature: adhoc.nature,
                        evs: adhoc.evs,
                        ivs: adhoc.ivs,
                    };
                };
                // 片方がマイポケモンの場合は通常エンドポイントを使う
                if (this.attackerMode === 'my' && this.defenderMode === 'my') {
                    url = '/api/v1/damage-calc';
                    payload = {
                        attacker_id: parseInt(this.attackerId),
                        defender_id: parseInt(this.defenderId),
                        move_id: parseInt(this.moveId),
                        attacker_rank: this.attackerRank,
                        defender_rank: this.defenderRank,
                        weather: this.weather, terrain: this.terrain,
                        is_critical: this.isCritical, other_modifiers: modifiers,
                    };
                } else {
                    // アドホック用: 両方のデータをまとめて送る
                    const atk = this.attackerMode === 'adhoc' ? this.adhocAttacker : null;
                    const def = this.defenderMode === 'adhoc' ? this.adhocDefender : null;

                    // 片方がマイポケモンのときは事前にその実数値を取得
                    let atkData, defData;
                    if (this.attackerMode === 'my') {
                        const info = this.attackerInfo;
                        atkData = {
                            pokemon_id: info.pokemon_id,
                            level: info.level,
                            nature: info.nature,
                            evs: {hp:info.ev_hp,attack:info.ev_attack,defense:info.ev_defense,sp_attack:info.ev_sp_attack,sp_defense:info.ev_sp_defense,speed:info.ev_speed},
                            ivs: {hp:info.iv_hp,attack:info.iv_attack,defense:info.iv_defense,sp_attack:info.iv_sp_attack,sp_defense:info.iv_sp_defense,speed:info.iv_speed},
                        };
                    } else {
                        atkData = {pokemon_id: parseInt(atk.pokemon_id), level: atk.level, nature: atk.nature, evs: atk.evs, ivs: atk.ivs};
                    }
                    if (this.defenderMode === 'my') {
                        const info = this.defenderInfo;
                        defData = {
                            pokemon_id: info.pokemon_id,
                            level: info.level,
                            nature: info.nature,
                            evs: {hp:info.ev_hp,attack:info.ev_attack,defense:info.ev_defense,sp_attack:info.ev_sp_attack,sp_defense:info.ev_sp_defense,speed:info.ev_speed},
                            ivs: {hp:info.iv_hp,attack:info.iv_attack,defense:info.iv_defense,sp_attack:info.iv_sp_attack,sp_defense:info.iv_sp_defense,speed:info.iv_speed},
                        };
                    } else {
                        defData = {pokemon_id: parseInt(def.pokemon_id), level: def.level, nature: def.nature, evs: def.evs, ivs: def.ivs};
                    }

                    payload = {
                        attacker: atkData, defender: defData,
                        move_id: parseInt(this.moveId),
                        attacker_rank: this.attackerRank, defender_rank: this.defenderRank,
                        weather: this.weather, terrain: this.terrain,
                        is_critical: this.isCritical, other_modifiers: modifiers,
                    };
                }
            } else {
                url = '/api/v1/damage-calc';
                payload = {
                    attacker_id: parseInt(this.attackerId),
                    defender_id: parseInt(this.defenderId),
                    move_id: parseInt(this.moveId),
                    attacker_rank: this.attackerRank, defender_rank: this.defenderRank,
                    weather: this.weather, terrain: this.terrain,
                    is_critical: this.isCritical, other_modifiers: modifiers,
                };
            }

            const res = await fetch(url, {
                method: 'POST',
                headers: {'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},
                body: JSON.stringify(payload),
            });
            this.result = await res.json();
        },

        saveToMemo() {
            if (!this.result) return;
            // ラベルを自動生成
            const atkName = this.attackerMode === 'my'
                ? (this.attackerInfo?.display_name || '攻撃')
                : (this.adhocAttacker.basePokemon?.name_ja || '攻撃');
            const defName = this.defenderMode === 'my'
                ? (this.defenderInfo?.display_name || '防御')
                : (this.adhocDefender.basePokemon?.name_ja || '防御');
            const moveName = this.selectedMove?.name_ja || 'わざ';
            const label = `${atkName} → ${moveName} → ${defName}`;
            this.memos.push({
                label,
                damage_min: this.result.damage_min,
                damage_max: this.result.damage_max,
                damage_percent_min: this.result.damage_percent_min,
                damage_percent_max: this.result.damage_percent_max,
                type_effectiveness: this.result.type_effectiveness,
                one_shot: this.result.one_shot,
                two_shot: this.result.two_shot,
            });
        },
    };
}
</script>
@endpush
