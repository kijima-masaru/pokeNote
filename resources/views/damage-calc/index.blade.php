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
                        <label class="form-check-label" for="burnCheck" style="font-size:.85rem">やけど（物理半減）</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" x-model="grounded" id="groundedCheck">
                        <label class="form-check-label" for="groundedCheck" style="font-size:.85rem">地面にいる（フィールド有効）</label>
                    </div>
                    <hr class="my-2">
                    <div class="fw-semibold mb-1" style="font-size:.8rem"><i class="bi bi-shield-shaded"></i> 壁（防御側）</div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" x-model="reflect" id="reflectCheck">
                        <label class="form-check-label" for="reflectCheck" style="font-size:.85rem">リフレクター（物理半減）</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" x-model="lightScreen" id="lightScreenCheck">
                        <label class="form-check-label" for="lightScreenCheck" style="font-size:.85rem">ひかりのかべ（特殊半減）</label>
                    </div>
                    <hr class="my-2">
                    <div class="fw-semibold mb-1" style="font-size:.8rem"><i class="bi bi-plus-circle"></i> 追加ダメージ</div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" x-model="extraStealthRock" id="srCheck">
                        <label class="form-check-label" for="srCheck" style="font-size:.85rem">ステルスロック</label>
                    </div>
                    <div class="d-flex align-items-center gap-1 mb-1">
                        <span style="font-size:.85rem">まきびし</span>
                        <select class="form-select form-select-sm" x-model="spikesLevel" style="width:auto">
                            <option value="0">なし</option>
                            <option value="1">1枚（1/8）</option>
                            <option value="2">2枚（1/6）</option>
                            <option value="3">3枚（1/4）</option>
                        </select>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" x-model="extraDisguise" id="disguiseCheck">
                        <label class="form-check-label" for="disguiseCheck" style="font-size:.85rem">ばけのかわ（HP×1/8）</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" x-model="extraRockyHelmet" id="rockyCheck">
                        <label class="form-check-label" for="rockyCheck" style="font-size:.85rem">ゴツゴツメット（攻撃側HP×1/6）</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" x-model="extraLifeOrb" id="lifeOrbCheck">
                        <label class="form-check-label" for="lifeOrbCheck" style="font-size:.85rem">いのちのたま（×1.3・反動HP×1/10）</label>
                    </div>
                    <hr class="my-2">
                    <div class="fw-semibold mb-1" style="font-size:.8rem"><i class="bi bi-heart-half"></i> 防御側残りHP</div>
                    <div class="d-flex align-items-center gap-2">
                        <input type="range" class="form-range flex-grow-1" min="1" max="100"
                               x-model.number="defenderHpPercent" style="flex:1">
                        <span class="fw-bold" style="min-width:42px;font-size:.9rem" x-text="defenderHpPercent+'%'"></span>
                    </div>
                    <div class="text-muted" style="font-size:.72rem">（満タン=100%、残り半分=50% など）</div>
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
                    <!-- HP残量表示 -->
                    <div class="mb-2" x-show="defenderHpPercent < 100">
                        <div class="text-muted mb-1" style="font-size:.8rem">
                            防御側現在HP: <span class="fw-bold" x-text="result?.defender_hp_current"></span>
                            / <span x-text="result?.defender_hp_max"></span>
                            (<span x-text="defenderHpPercent"></span>%)
                        </div>
                    </div>
                    <div>
                        <div class="text-muted mb-1" style="font-size:.8rem">乱数16本</div>
                        <div class="d-flex gap-1 flex-wrap">
                            <template x-for="(roll, i) in (result?.rolls||[])" :key="i">
                                <div class="text-center px-2 py-1 rounded"
                                     :class="roll >= (result?.defender_hp_current||defenderHpVal) ? 'bg-danger text-white' : 'bg-light border'"
                                     style="min-width:42px;font-size:.8rem" x-text="roll"></div>
                            </template>
                        </div>
                    </div>
                    <!-- 追加ダメージ -->
                    <div class="mt-2" x-show="(result?.additional_damage||[]).length > 0">
                        <div class="text-muted mb-1" style="font-size:.8rem"><i class="bi bi-plus-circle"></i> 追加ダメージ</div>
                        <div class="d-flex flex-wrap gap-2">
                            <template x-for="(ad, ai) in (result?.additional_damage||[])" :key="ai">
                                <div class="badge bg-warning text-dark px-2 py-1" style="font-size:.8rem">
                                    <span x-text="ad.label"></span>:
                                    <span x-show="ad.damage !== null" x-text="ad.damage+'ダメ'"></span>
                                    (<span x-text="ad.percent+'%'"></span>)
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 連続計算モード -->
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center"
                     style="cursor:pointer" @click="multiCalcOpen=!multiCalcOpen">
                    <strong><i class="bi bi-table"></i> 連続計算モード <span class="badge bg-secondary ms-1" style="font-size:.7rem">複数技・相手を一括比較</span></strong>
                    <i class="bi" :class="multiCalcOpen?'bi-chevron-up':'bi-chevron-down'"></i>
                </div>
                <div x-show="multiCalcOpen" x-collapse>
                    <div class="card-body">
                        <p class="text-muted mb-2" style="font-size:.82rem">技リストや相手ポケモンリストを追加して一括計算します。攻撃側は現在設定中の攻撃側を使用します。</p>
                        <div class="row g-2 mb-2">
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <strong style="font-size:.85rem">技リスト</strong>
                                    <button class="btn btn-xs btn-outline-secondary" style="font-size:.75rem;padding:1px 8px" @click="multiMoves.push({id:'',name:''})">+ 追加</button>
                                </div>
                                <template x-for="(mm, mi) in multiMoves" :key="mi">
                                    <div class="d-flex gap-1 mb-1">
                                        <select class="form-select form-select-sm" x-model="mm.id" @change="mm.name=moveList.find(m=>m.id==mm.id)?.name_ja||''">
                                            <option value="">-- 技を選択 --</option>
                                            <template x-for="m in moveList" :key="m.id">
                                                <option :value="m.id" x-text="m.name_ja"></option>
                                            </template>
                                        </select>
                                        <button class="btn btn-xs btn-outline-danger" style="font-size:.7rem;padding:1px 6px" @click="multiMoves.splice(mi,1)">✕</button>
                                    </div>
                                </template>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <strong style="font-size:.85rem">相手ポケモンリスト</strong>
                                    <button class="btn btn-xs btn-outline-secondary" style="font-size:.75rem;padding:1px 8px" @click="multiDefenders.push({id:'',name:''})">+ 追加</button>
                                </div>
                                <template x-for="(md, di) in multiDefenders" :key="di">
                                    <div class="d-flex gap-1 mb-1">
                                        <select class="form-select form-select-sm" x-model="md.id" @change="md.name=myPokemonList.find(p=>p.id==md.id)?.display_name||''">
                                            <option value="">-- マイポケモンから選択 --</option>
                                            @foreach($myPokemonList as $cp)
                                                <option value="{{ $cp->id }}">{{ $cp->display_name }}</option>
                                            @endforeach
                                        </select>
                                        <button class="btn btn-xs btn-outline-danger" style="font-size:.7rem;padding:1px 6px" @click="multiDefenders.splice(di,1)">✕</button>
                                    </div>
                                </template>
                            </div>
                        </div>
                        <div class="text-center mb-2">
                            <button class="btn btn-warning btn-sm px-4 fw-bold" @click="runMultiCalc()" :disabled="multiCalcLoading">
                                <span x-show="multiCalcLoading" class="spinner-border spinner-border-sm me-1"></span>
                                <i class="bi bi-play-fill"></i> 一括計算
                            </button>
                        </div>
                        <div x-show="multiResults.length > 0" class="table-responsive">
                            <table class="table table-sm table-hover mb-0" style="font-size:.8rem">
                                <thead class="table-light">
                                    <tr>
                                        <th>技</th>
                                        <th>相手</th>
                                        <th class="text-center">相性</th>
                                        <th class="text-center">ダメージ</th>
                                        <th class="text-center">割合</th>
                                        <th class="text-center">確定</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(r, ri) in multiResults" :key="ri">
                                        <tr :class="r.one_shot?'table-danger':r.two_shot?'table-warning':''">
                                            <td x-text="r.move_name"></td>
                                            <td x-text="r.defender_name"></td>
                                            <td class="text-center" x-text="r.type_effectiveness+'x'"></td>
                                            <td class="text-center" x-text="r.damage_min+'~'+r.damage_max"></td>
                                            <td class="text-center" x-text="r.damage_percent_min+'%~'+r.damage_percent_max+'%'"></td>
                                            <td class="text-center">
                                                <span x-show="r.one_shot" class="badge bg-danger">確定1発</span>
                                                <span x-show="!r.one_shot && r.two_shot" class="badge bg-warning text-dark">確定2発</span>
                                                <span x-show="!r.one_shot && !r.two_shot" class="text-muted">3発以上</span>
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
        grounded: false,
        reflect: false, lightScreen: false,
        extraStealthRock: false, spikesLevel: 0,
        extraDisguise: false, extraRockyHelmet: false, extraLifeOrb: false,
        defenderHpPercent: 100,
        attackerRank: {attack:0, sp_attack:0},
        defenderRank: {defense:0, sp_defense:0},
        result: null,
        memos: [],
        rankOptions: [-6,-5,-4,-3,-2,-1,0,1,2,3,4,5,6],
        // 連続計算モード
        multiCalcOpen: false,
        multiMoves: [{id:'',name:''}],
        multiDefenders: [{id:'',name:''}],
        multiResults: [],
        multiCalcLoading: false,
        moveList: [],

        get defenderHpVal() {
            const hp = this.defenderMode === 'adhoc'
                ? (this.adhocDefender.stats?.hp || 0)
                : (this.defenderInfo?.actual_stats?.hp || 0);
            return hp > 0 ? Math.max(1, Math.floor(hp * this.defenderHpPercent / 100)) : Infinity;
        },

        get canCalculate() {
            const atkOk = this.attackerMode === 'my' ? !!this.attackerId : !!this.adhocAttacker.pokemon_id;
            const defOk = this.defenderMode === 'my' ? !!this.defenderId : !!this.adhocDefender.pokemon_id;
            return atkOk && defOk && !!this.moveId;
        },

        async init() {
            if (this.attackerId) await this.loadAttacker();
            if (this.defenderId) await this.loadDefender();
            // 連続計算用わざリストをロード
            const mres = await fetch('/api/v1/moves?per_page=500');
            const mdata = await mres.json();
            this.moveList = mdata.data || [];
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

        buildModifiers() {
            const m = [];
            if (this.burned)      m.push('burned');
            if (this.reflect)     m.push('reflect');
            if (this.lightScreen) m.push('light_screen');
            if (this.grounded)    m.push('grounded');
            return m;
        },

        buildExtraDamage() {
            const e = [];
            if (this.extraStealthRock) e.push('stealth_rock');
            if (this.spikesLevel == 1) e.push('spikes_1');
            if (this.spikesLevel == 2) e.push('spikes_2');
            if (this.spikesLevel == 3) e.push('spikes_3');
            if (this.extraDisguise)    e.push('disguise');
            if (this.extraRockyHelmet) e.push('rocky_helmet');
            if (this.extraLifeOrb)     e.push('life_orb');
            return e;
        },

        async calculate() {
            const modifiers = this.buildModifiers();

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
                        extra_damage: this.buildExtraDamage(),
                        defender_hp_percent: this.defenderHpPercent / 100,
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
                        extra_damage: this.buildExtraDamage(),
                        defender_hp_percent: this.defenderHpPercent / 100,
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
                    extra_damage: this.buildExtraDamage(),
                    defender_hp_percent: this.defenderHpPercent / 100,
                };
            }

            const res = await fetch(url, {
                method: 'POST',
                headers: {'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},
                body: JSON.stringify(payload),
            });
            this.result = await res.json();
        },

        async runMultiCalc() {
            const moves    = this.multiMoves.filter(m => m.id);
            const defenders = this.multiDefenders.filter(d => d.id);
            if (moves.length === 0 || defenders.length === 0 || !this.attackerId) {
                alert('攻撃側マイポケモン・技・相手ポケモンをそれぞれ1件以上設定してください');
                return;
            }
            this.multiCalcLoading = true;
            this.multiResults = [];
            const csrfToken = document.querySelector('meta[name=csrf-token]').content;
            const modifiers = this.burned ? ['burned'] : [];

            for (const mv of moves) {
                for (const df of defenders) {
                    try {
                        const payload = {
                            attacker_id: parseInt(this.attackerId),
                            defender_id: parseInt(df.id),
                            move_id: parseInt(mv.id),
                            attacker_rank: this.attackerRank,
                            defender_rank: this.defenderRank,
                            weather: this.weather,
                            terrain: this.terrain,
                            is_critical: this.isCritical,
                            other_modifiers: modifiers,
                        };
                        const res  = await fetch('/api/v1/damage-calc', {
                            method: 'POST',
                            headers: {'Content-Type':'application/json','X-CSRF-TOKEN': csrfToken},
                            body: JSON.stringify(payload),
                        });
                        const data = await res.json();
                        const moveName    = this.moveList.find(m => m.id == mv.id)?.name_ja || mv.id;
                        const defenderName = @json($myPokemonList->pluck('display_name', 'id'));
                        this.multiResults.push({
                            move_name: moveName,
                            defender_name: defenderName[df.id] || df.id,
                            ...data,
                        });
                    } catch {}
                }
            }
            this.multiCalcLoading = false;
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
