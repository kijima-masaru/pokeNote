@extends('layouts.app')
@section('title', 'タイプ相性早見表')
@section('content')
<div x-data="typeChartApp()" x-init="init()">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-grid-3x3"></i> タイプ相性早見表</h4>
        <div class="d-flex gap-2 align-items-center">
            <small class="text-muted">行 = 攻撃側 / 列 = 防御側</small>
        </div>
    </div>

    <!-- タイプ選択（防御タイプ1・タイプ2を選んで複合相性を確認） -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2">
            <div class="row g-2 align-items-end">
                <div class="col-auto">
                    <label class="form-label mb-1" style="font-size:.8rem">防御タイプ1</label>
                    <select class="form-select form-select-sm" x-model="defType1" style="width:130px">
                        <option value="">なし</option>
                        @foreach($types as $t)
                        <option value="{{ $t->value }}">{{ $t->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <label class="form-label mb-1" style="font-size:.8rem">防御タイプ2</label>
                    <select class="form-select form-select-sm" x-model="defType2" style="width:130px">
                        <option value="">なし</option>
                        @foreach($types as $t)
                        <option value="{{ $t->value }}">{{ $t->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <button class="btn btn-primary btn-sm" @click="calcDefense()">相性を確認</button>
                    <button class="btn btn-outline-secondary btn-sm ms-1" @click="defType1='';defType2='';defenseResult=null">クリア</button>
                </div>
            </div>
            <template x-if="defenseResult">
                <div class="mt-3">
                    <div class="d-flex flex-wrap gap-2">
                        <template x-for="[type, eff] in defenseResult" :key="type">
                            <div class="text-center">
                                <span class="type-badge d-block mb-1" :class="'type-'+type" style="font-size:.75rem" x-text="typeLabel(type)"></span>
                                <span class="badge"
                                      :class="eff > 1 ? 'bg-danger' : (eff === 0 ? 'bg-dark' : (eff < 1 ? 'bg-primary' : 'bg-secondary'))"
                                      style="font-size:.8rem" x-text="effText(eff)"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- 全タイプ相性表 -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <strong>全タイプ相性表</strong>
            <small class="text-muted ms-2">
                <span class="badge bg-danger">×2</span>
                <span class="badge bg-warning text-dark ms-1">×4</span>
                <span class="badge bg-primary ms-1">×½</span>
                <span class="badge bg-dark ms-1">×0</span>
                <span class="text-muted ms-1">（無印=等倍）</span>
            </small>
        </div>
        <div class="card-body p-0" style="overflow-x:auto">
            <table class="table table-bordered table-sm mb-0" style="font-size:.68rem;min-width:800px">
                <thead class="table-dark">
                    <tr>
                        <th style="min-width:70px">攻↓ / 防→</th>
                        @foreach($types as $t)
                        <th class="text-center px-1" style="min-width:42px">
                            <span class="type-badge type-{{ $t->value }}" style="font-size:.6rem;padding:1px 4px">{{ $t->label() }}</span>
                        </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($types as $atk)
                    <tr>
                        <td class="fw-semibold">
                            <span class="type-badge type-{{ $atk->value }}" style="font-size:.6rem;padding:1px 4px">{{ $atk->label() }}</span>
                        </td>
                        @foreach($types as $def)
                        @php
                            $eff = \App\Services\DamageCalculatorService::singleTypeEffectiveness($atk->value, $def->value);
                        @endphp
                        <td class="text-center px-0"
                            style="@if($eff > 1) background:#dc354533 @elseif($eff === 0.0) background:#00000022 @elseif($eff < 1) background:#0d6efd22 @endif">
                            @if($eff == 2)<span class="text-danger fw-bold">2</span>
                            @elseif($eff == 0.5)<span class="text-primary">½</span>
                            @elseif($eff == 0)<span class="fw-bold">0</span>
                            @else<span class="text-muted" style="font-size:.6rem">-</span>
                            @endif
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
const TYPE_CHART = {
    normal:    {rock:.5, steel:.5, ghost:0},
    fire:      {fire:.5, water:.5, rock:.5, dragon:.5, grass:2, ice:2, bug:2, steel:2},
    water:     {water:.5, grass:.5, dragon:.5, fire:2, ground:2, rock:2},
    electric:  {electric:.5, grass:.5, dragon:.5, ground:0, flying:2, water:2},
    grass:     {fire:.5, grass:.5, poison:.5, flying:.5, bug:.5, dragon:.5, steel:.5, water:2, ground:2, rock:2},
    ice:       {water:.5, ice:.5, fire:2, fighting:2, rock:2, steel:2, grass:2, dragon:2, ground:2, flying:2},
    fighting:  {poison:.5, flying:.5, psychic:.5, bug:.5, fairy:.5, ghost:0, normal:2, rock:2, steel:2, ice:2, dark:2},
    poison:    {poison:.5, ground:.5, rock:.5, ghost:.5, steel:0, grass:2, fairy:2},
    ground:    {grass:.5, bug:.5, flying:0, fire:2, electric:2, poison:2, rock:2, steel:2},
    flying:    {electric:.5, rock:.5, steel:.5, fighting:2, bug:2, grass:2},
    psychic:   {psychic:.5, steel:.5, dark:0, fighting:2, poison:2},
    bug:       {fire:.5, fighting:.5, flying:.5, ghost:.5, steel:.5, fairy:.5, grass:2, psychic:2, dark:2},
    rock:      {fighting:.5, ground:.5, steel:.5, fire:2, ice:2, flying:2, bug:2},
    ghost:     {normal:0, dark:.5, psychic:2, ghost:2},
    dragon:    {steel:.5, fairy:0, dragon:2},
    dark:      {fighting:.5, dark:.5, fairy:.5, psychic:2, ghost:2},
    steel:     {fire:.5, water:.5, electric:.5, steel:.5, rock:2, ice:2, fairy:2},
    fairy:     {fire:.5, poison:.5, steel:.5, dragon:0, fighting:2, dark:2},
};

const TYPE_LABELS = {
    normal:'ノーマル',fire:'ほのお',water:'みず',electric:'でんき',grass:'くさ',
    ice:'こおり',fighting:'かくとう',poison:'どく',ground:'じめん',flying:'ひこう',
    psychic:'エスパー',bug:'むし',rock:'いわ',ghost:'ゴースト',dragon:'ドラゴン',
    dark:'あく',steel:'はがね',fairy:'フェアリー'
};

function typeChartApp() {
    return {
        defType1: '',
        defType2: '',
        defenseResult: null,

        typeLabel(t) { return TYPE_LABELS[t] || t; },
        effText(e) {
            if (e === 0)   return '×0';
            if (e === 0.25) return '×¼';
            if (e === 0.5) return '×½';
            if (e === 2)   return '×2';
            if (e === 4)   return '×4';
            return '×' + e;
        },

        init() {},

        calcDefense() {
            if (!this.defType1) return;
            const allTypes = Object.keys(TYPE_LABELS);
            const result = allTypes.map(atk => {
                let eff = TYPE_CHART[atk]?.[this.defType1] ?? 1;
                if (this.defType2) {
                    eff *= TYPE_CHART[atk]?.[this.defType2] ?? 1;
                }
                return [atk, eff];
            }).sort((a, b) => b[1] - a[1]);
            this.defenseResult = result;
        },
    };
}
</script>
@endpush
