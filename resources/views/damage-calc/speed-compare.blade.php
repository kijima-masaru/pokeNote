@extends('layouts.app')
@section('title', '素早さ比較')
@section('content')
<div x-data="speedCompare()" x-init="init()">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-speedometer2"></i> 素早さ比較</h4>
        <a href="{{ route('damage-calc.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-calculator"></i> ダメージ計算へ
        </a>
    </div>

    <div class="row g-3 mb-3">
        <!-- ポケモンA -->
        <div class="col-md-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-success text-white">
                    <strong>ポケモン A</strong>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <label class="form-label mb-1" style="font-size:.8rem">マイポケモンから選択</label>
                        <select class="form-select form-select-sm" x-model="pokemonAId" @change="loadPokemon('A')">
                            <option value="">選択してください</option>
                            @foreach($myPokemonList as $cp)
                            <option value="{{ $cp->id }}">{{ $cp->display_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label mb-1" style="font-size:.8rem">種族値</label>
                            <input type="number" class="form-control form-control-sm" x-model.number="a.base_speed" min="1" max="255" @input="calc()">
                        </div>
                        <div class="col-6">
                            <label class="form-label mb-1" style="font-size:.8rem">個体値</label>
                            <input type="number" class="form-control form-control-sm" x-model.number="a.iv" min="0" max="31" @input="calc()">
                        </div>
                        <div class="col-6">
                            <label class="form-label mb-1" style="font-size:.8rem">努力値</label>
                            <input type="number" class="form-control form-control-sm" x-model.number="a.ev" min="0" max="252" step="4" @input="calc()">
                        </div>
                        <div class="col-6">
                            <label class="form-label mb-1" style="font-size:.8rem">レベル</label>
                            <input type="number" class="form-control form-control-sm" x-model.number="a.level" min="1" max="100" @input="calc()">
                        </div>
                        <div class="col-12">
                            <label class="form-label mb-1" style="font-size:.8rem">性格</label>
                            <select class="form-select form-select-sm" x-model="a.nature" @change="calc()">
                                @foreach($natures as $n)
                                <option value="{{ $n->value }}">{{ $n->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label mb-1" style="font-size:.8rem">ランク補正</label>
                            <select class="form-select form-select-sm" x-model.number="a.rank" @change="calc()">
                                @foreach(range(-6, 6) as $r)
                                <option value="{{ $r }}">{{ $r > 0 ? '+' : '' }}{{ $r }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label mb-1" style="font-size:.8rem">おいかぜ</label>
                            <div class="form-check form-switch mt-1">
                                <input class="form-check-input" type="checkbox" x-model="a.tailwind" @change="calc()">
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="form-label mb-1" style="font-size:.8rem">麻痺</label>
                            <div class="form-check form-switch mt-1">
                                <input class="form-check-input" type="checkbox" x-model="a.paralysis" @change="calc()">
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 text-center p-3 rounded" :class="resultA > resultB ? 'bg-success bg-opacity-10 border border-success' : 'bg-light'">
                        <div class="text-muted" style="font-size:.8rem">実数値</div>
                        <div class="fw-bold" style="font-size:2rem" :style="resultA > resultB ? 'color:#198754' : ''" x-text="resultA"></div>
                        <div x-show="resultA > resultB" class="badge bg-success">先攻</div>
                        <div x-show="resultA < resultB" class="badge bg-secondary">後攻</div>
                        <div x-show="resultA === resultB" class="badge bg-warning text-dark">同速</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- VS -->
        <div class="col-md-2 d-flex align-items-center justify-content-center">
            <div class="text-center">
                <div class="fw-bold text-muted" style="font-size:1.5rem">VS</div>
                <button class="btn btn-outline-secondary btn-sm mt-2" @click="swapAB()" title="入れ替え">
                    <i class="bi bi-arrow-left-right"></i>
                </button>
            </div>
        </div>

        <!-- ポケモンB -->
        <div class="col-md-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-danger text-white">
                    <strong>ポケモン B</strong>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <label class="form-label mb-1" style="font-size:.8rem">マイポケモンから選択</label>
                        <select class="form-select form-select-sm" x-model="pokemonBId" @change="loadPokemon('B')">
                            <option value="">選択してください</option>
                            @foreach($myPokemonList as $cp)
                            <option value="{{ $cp->id }}">{{ $cp->display_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label mb-1" style="font-size:.8rem">種族値</label>
                            <input type="number" class="form-control form-control-sm" x-model.number="b.base_speed" min="1" max="255" @input="calc()">
                        </div>
                        <div class="col-6">
                            <label class="form-label mb-1" style="font-size:.8rem">個体値</label>
                            <input type="number" class="form-control form-control-sm" x-model.number="b.iv" min="0" max="31" @input="calc()">
                        </div>
                        <div class="col-6">
                            <label class="form-label mb-1" style="font-size:.8rem">努力値</label>
                            <input type="number" class="form-control form-control-sm" x-model.number="b.ev" min="0" max="252" step="4" @input="calc()">
                        </div>
                        <div class="col-6">
                            <label class="form-label mb-1" style="font-size:.8rem">レベル</label>
                            <input type="number" class="form-control form-control-sm" x-model.number="b.level" min="1" max="100" @input="calc()">
                        </div>
                        <div class="col-12">
                            <label class="form-label mb-1" style="font-size:.8rem">性格</label>
                            <select class="form-select form-select-sm" x-model="b.nature" @change="calc()">
                                @foreach($natures as $n)
                                <option value="{{ $n->value }}">{{ $n->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label mb-1" style="font-size:.8rem">ランク補正</label>
                            <select class="form-select form-select-sm" x-model.number="b.rank" @change="calc()">
                                @foreach(range(-6, 6) as $r)
                                <option value="{{ $r }}">{{ $r > 0 ? '+' : '' }}{{ $r }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label mb-1" style="font-size:.8rem">おいかぜ</label>
                            <div class="form-check form-switch mt-1">
                                <input class="form-check-input" type="checkbox" x-model="b.tailwind" @change="calc()">
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="form-label mb-1" style="font-size:.8rem">麻痺</label>
                            <div class="form-check form-switch mt-1">
                                <input class="form-check-input" type="checkbox" x-model="b.paralysis" @change="calc()">
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 text-center p-3 rounded" :class="resultB > resultA ? 'bg-danger bg-opacity-10 border border-danger' : 'bg-light'">
                        <div class="text-muted" style="font-size:.8rem">実数値</div>
                        <div class="fw-bold" style="font-size:2rem" :style="resultB > resultA ? 'color:#dc3545' : ''" x-text="resultB"></div>
                        <div x-show="resultB > resultA" class="badge bg-danger">先攻</div>
                        <div x-show="resultB < resultA" class="badge bg-secondary">後攻</div>
                        <div x-show="resultA === resultB" class="badge bg-warning text-dark">同速</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 差分表示 -->
    <div class="card border-0 shadow-sm" x-show="resultA > 0 || resultB > 0">
        <div class="card-body text-center">
            <div class="fw-bold" style="font-size:1.1rem">
                <span x-show="resultA !== resultB">
                    差: <span class="text-primary fw-bold" x-text="Math.abs(resultA - resultB)"></span>
                    &nbsp;—&nbsp;
                    <span x-text="resultA > resultB ? 'A が先攻' : 'B が先攻'"></span>
                </span>
                <span x-show="resultA === resultB" class="text-warning">同速（コイントス）</span>
            </div>
            <div class="text-muted mt-1" style="font-size:.85rem">
                Aが先攻するために必要な素早さ実数値: <strong x-text="resultB + 1"></strong>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
const NATURE_BOOSTS = {
    lonely:{attack:1.1},brave:{attack:1.1},adamant:{attack:1.1},naughty:{attack:1.1},
    bold:{defense:1.1},relaxed:{defense:1.1},impish:{defense:1.1},lax:{defense:1.1},
    modest:{sp_attack:1.1},mild:{sp_attack:1.1},quiet:{sp_attack:1.1},rash:{sp_attack:1.1},
    calm:{sp_defense:1.1},gentle:{sp_defense:1.1},sassy:{sp_defense:1.1},careful:{sp_defense:1.1},
    timid:{speed:1.1},hasty:{speed:1.1},jolly:{speed:1.1},naive:{speed:1.1},
    lonely:{attack:1.1,defense:0.9},brave:{attack:1.1,speed:0.9},
    adamant:{attack:1.1,sp_attack:0.9},naughty:{attack:1.1,sp_defense:0.9},
    bold:{defense:1.1,attack:0.9},relaxed:{defense:1.1,speed:0.9},
    impish:{defense:1.1,sp_attack:0.9},lax:{defense:1.1,sp_defense:0.9},
    modest:{sp_attack:1.1,attack:0.9},mild:{sp_attack:1.1,defense:0.9},
    quiet:{sp_attack:1.1,speed:0.9},rash:{sp_attack:1.1,sp_defense:0.9},
    calm:{sp_defense:1.1,attack:0.9},gentle:{sp_defense:1.1,defense:0.9},
    sassy:{sp_defense:1.1,speed:0.9},careful:{sp_defense:1.1,sp_attack:0.9},
    timid:{speed:1.1,attack:0.9},hasty:{speed:1.1,defense:0.9},
    jolly:{speed:1.1,sp_attack:0.9},naive:{speed:1.1,sp_defense:0.9},
};

function calcSpeed({base_speed, iv, ev, level, nature, rank, tailwind, paralysis}) {
    const natureBoost = NATURE_BOOSTS[nature]?.speed ?? 1;
    const stat = Math.floor((Math.floor((base_speed * 2 + iv + Math.floor(ev / 4)) * level / 100) + 5) * natureBoost);
    let result = stat;
    // ランク補正
    if (rank > 0) result = Math.floor(stat * (2 + rank) / 2);
    else if (rank < 0) result = Math.floor(stat * 2 / (2 - rank));
    // おいかぜ
    if (tailwind) result = result * 2;
    // 麻痺
    if (paralysis) result = Math.floor(result * 0.5);
    return result;
}

function speedCompare() {
    const defaultPoke = () => ({ base_speed: 80, iv: 31, ev: 0, level: 50, nature: 'hardy', rank: 0, tailwind: false, paralysis: false });
    return {
        pokemonAId: '',
        pokemonBId: '',
        a: defaultPoke(),
        b: defaultPoke(),
        resultA: 0,
        resultB: 0,

        init() { this.calc(); },

        calc() {
            this.resultA = calcSpeed(this.a);
            this.resultB = calcSpeed(this.b);
        },

        async loadPokemon(side) {
            const id = side === 'A' ? this.pokemonAId : this.pokemonBId;
            if (!id) return;
            const res = await fetch(`/api/v1/custom-pokemon/${id}`, {
                headers: {'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content},
            });
            const cp = await res.json();
            const poke = {
                base_speed: cp.pokemon.speed ?? 80,
                iv: cp.iv_speed ?? 31,
                ev: cp.ev_speed ?? 0,
                level: cp.level ?? 50,
                nature: cp.nature ?? 'hardy',
                rank: 0,
                tailwind: false,
                paralysis: false,
            };
            if (side === 'A') this.a = poke;
            else              this.b = poke;
            this.calc();
        },

        swapAB() {
            [this.a, this.b] = [this.b, this.a];
            [this.pokemonAId, this.pokemonBId] = [this.pokemonBId, this.pokemonAId];
            this.calc();
        },
    };
}
</script>
@endpush
