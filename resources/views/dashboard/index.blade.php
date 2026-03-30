@extends('layouts.app')
@section('title', 'ダッシュボード')
@section('content')
<div x-data="dashboard()" x-init="init()">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-house"></i> ダッシュボード</h4>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-secondary" @click="toggleEdit()">
                <i class="bi" :class="editMode ? 'bi-check-lg' : 'bi-grid-1x2'"></i>
                <span x-text="editMode ? '完了' : 'レイアウト編集'"></span>
            </button>
        </div>
    </div>

    <!-- ウィジェット選択（編集モード時） -->
    <div x-show="editMode" class="card border-warning shadow-sm mb-3" x-cloak>
        <div class="card-header bg-warning text-dark">
            <strong><i class="bi bi-sliders"></i> ウィジェット設定</strong>
            <small class="ms-2">ドラッグで並べ替え、チェックで表示/非表示を切替</small>
        </div>
        <div class="card-body py-2">
            <div class="d-flex flex-wrap gap-2">
                <template x-for="w in allWidgets" :key="w.id">
                    <label class="d-flex align-items-center gap-1 px-3 py-1 border rounded" style="cursor:pointer;font-size:.85rem"
                           :class="isVisible(w.id) ? 'border-success bg-success bg-opacity-10' : 'border-secondary'">
                        <input type="checkbox" :checked="isVisible(w.id)" @change="toggleWidget(w.id)">
                        <span x-text="w.label"></span>
                    </label>
                </template>
            </div>
        </div>
    </div>

    <!-- ウィジェットグリッド -->
    <div id="widget-container" class="row g-3">

        <!-- クイックリンク -->
        <div class="col-12" data-widget-id="quicklinks" x-show="isVisible('quicklinks')">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <strong>クイックリンク</strong>
                    <i class="bi bi-grip-vertical text-muted" x-show="editMode" style="cursor:grab"></i>
                </div>
                <div class="card-body p-2">
                    <div class="row g-2">
                        <div class="col-6 col-md-3">
                            <a href="{{ route('pokemon.index') }}" class="text-decoration-none">
                                <div class="card border-0 bg-light text-center p-3">
                                    <i class="bi bi-collection text-primary" style="font-size:1.5rem"></i>
                                    <div class="fw-bold mt-1" style="font-size:.85rem">ポケモン図鑑</div>
                                </div>
                            </a>
                        </div>
                        <div class="col-6 col-md-3">
                            <a href="{{ route('custom-pokemon.create') }}" class="text-decoration-none">
                                <div class="card border-0 bg-light text-center p-3">
                                    <i class="bi bi-plus-circle text-success" style="font-size:1.5rem"></i>
                                    <div class="fw-bold mt-1" style="font-size:.85rem">ポケモン登録</div>
                                </div>
                            </a>
                        </div>
                        <div class="col-6 col-md-3">
                            <a href="{{ route('damage-calc.index') }}" class="text-decoration-none">
                                <div class="card border-0 bg-light text-center p-3">
                                    <i class="bi bi-calculator text-warning" style="font-size:1.5rem"></i>
                                    <div class="fw-bold mt-1" style="font-size:.85rem">ダメージ計算</div>
                                </div>
                            </a>
                        </div>
                        <div class="col-6 col-md-3">
                            <a href="{{ route('battles.create') }}" class="text-decoration-none">
                                <div class="card border-0 bg-light text-center p-3">
                                    <i class="bi bi-trophy text-danger" style="font-size:1.5rem"></i>
                                    <div class="fw-bold mt-1" style="font-size:.85rem">対戦記録</div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 対戦統計 -->
        @php
            $finished = $battleStats->wins + $battleStats->loses + $battleStats->draws;
            $winRate = $finished > 0 ? round($battleStats->wins / $finished * 100, 1) : 0;
        @endphp
        <div class="col-12" data-widget-id="stats" x-show="isVisible('stats')">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <strong><i class="bi bi-bar-chart"></i> 対戦統計</strong>
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted" style="font-size:.85rem">通算 {{ $battleStats->total }}戦</span>
                        <i class="bi bi-grip-vertical text-muted" x-show="editMode" style="cursor:grab"></i>
                    </div>
                </div>
                <div class="card-body">
                    @if($battleStats->total > 0)
                    <div class="row g-3 align-items-center">
                        <div class="col-md-3">
                            <div class="d-flex gap-3 justify-content-center">
                                <div class="text-center">
                                    <div class="fw-bold text-success" style="font-size:1.8rem">{{ $battleStats->wins }}</div>
                                    <small class="text-muted">勝</small>
                                </div>
                                <div class="text-center">
                                    <div class="fw-bold text-danger" style="font-size:1.8rem">{{ $battleStats->loses }}</div>
                                    <small class="text-muted">負</small>
                                </div>
                                <div class="text-center">
                                    <div class="fw-bold text-secondary" style="font-size:1.8rem">{{ $battleStats->draws }}</div>
                                    <small class="text-muted">分</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="fw-bold" style="font-size:2rem;color:var(--poke-red)">{{ $winRate }}%</div>
                                <small class="text-muted">勝率</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            @if($recentResults->count() > 0)
                            <small class="text-muted d-block mb-1">直近{{ $recentResults->count() }}戦</small>
                            <div class="d-flex gap-1 flex-wrap">
                                @foreach($recentResults as $r)
                                    @if($r->result === 'win')<span class="badge bg-success" style="font-size:.75rem">勝</span>
                                    @elseif($r->result === 'lose')<span class="badge bg-danger" style="font-size:.75rem">負</span>
                                    @elseif($r->result === 'draw')<span class="badge bg-secondary" style="font-size:.75rem">分</span>
                                    @endif
                                @endforeach
                            </div>
                            @endif
                        </div>
                    </div>
                    @if($finished > 0)
                    <div class="mt-3">
                        <div class="progress" style="height:12px;border-radius:6px">
                            <div class="progress-bar bg-success" style="width:{{ $finished > 0 ? $battleStats->wins/$finished*100 : 0 }}%"></div>
                            <div class="progress-bar bg-secondary" style="width:{{ $finished > 0 ? $battleStats->draws/$finished*100 : 0 }}%"></div>
                            <div class="progress-bar bg-danger" style="width:{{ $finished > 0 ? $battleStats->loses/$finished*100 : 0 }}%"></div>
                        </div>
                    </div>
                    @endif
                    @else
                    <div class="text-center text-muted py-3">対戦データがありません</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- 最近の対戦 -->
        <div class="col-md-6" data-widget-id="recent-battles" x-show="isVisible('recent-battles')">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <strong><i class="bi bi-trophy"></i> 最近の対戦</strong>
                    <div class="d-flex gap-2">
                        <a href="{{ route('battles.index') }}" class="btn btn-sm btn-outline-secondary">一覧</a>
                        <i class="bi bi-grip-vertical text-muted" x-show="editMode" style="cursor:grab"></i>
                    </div>
                </div>
                <div class="card-body p-0">
                    @forelse($recentBattles as $battle)
                    <a href="{{ route('battles.show', $battle->id) }}" class="text-decoration-none text-dark">
                        <div class="d-flex align-items-center px-3 py-2 border-bottom">
                            @if($battle->result === 'win')<span class="badge bg-success me-2">勝</span>
                            @elseif($battle->result === 'lose')<span class="badge bg-danger me-2">負</span>
                            @elseif($battle->result === 'draw')<span class="badge bg-secondary me-2">分</span>
                            @else<span class="badge bg-light text-dark me-2">-</span>
                            @endif
                            <div class="flex-grow-1">
                                <div>{{ $battle->title ?? 'vs ' . ($battle->opponent_name ?? '名無し') }}</div>
                                <small class="text-muted">{{ $battle->turns_count }}ターン・{{ $battle->played_at?->format('m/d') ?? '-' }}</small>
                            </div>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </div>
                    </a>
                    @empty
                    <div class="text-center text-muted py-4">対戦記録がありません</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- マイポケモン -->
        <div class="col-md-6" data-widget-id="my-pokemon" x-show="isVisible('my-pokemon')">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <strong><i class="bi bi-star"></i> マイポケモン（最近）</strong>
                    <div class="d-flex gap-2">
                        <a href="{{ route('custom-pokemon.index') }}" class="btn btn-sm btn-outline-secondary">一覧</a>
                        <i class="bi bi-grip-vertical text-muted" x-show="editMode" style="cursor:grab"></i>
                    </div>
                </div>
                <div class="card-body p-0">
                    @forelse($recentCustomPokemon as $cp)
                    <a href="{{ route('custom-pokemon.show', $cp->id) }}" class="text-decoration-none text-dark">
                        <div class="d-flex align-items-center px-3 py-2 border-bottom">
                            @if($cp->pokemon->sprite_url)
                                <img src="{{ $cp->pokemon->sprite_url }}" alt="" style="width:40px;height:40px;object-fit:contain" class="me-2">
                            @else
                                <div class="bg-light rounded me-2 d-flex align-items-center justify-content-center" style="width:40px;height:40px">
                                    <i class="bi bi-question text-muted"></i>
                                </div>
                            @endif
                            <div class="flex-grow-1">
                                <div class="fw-semibold">{{ $cp->display_name }}</div>
                                <small class="text-muted">
                                    @foreach($cp->pokemon->types as $type)
                                        <span class="type-badge type-{{ $type->type }}">{{ \App\Enums\PokemonType::from($type->type)->label() }}</span>
                                    @endforeach
                                </small>
                            </div>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </div>
                    </a>
                    @empty
                    <div class="text-center text-muted py-4">マイポケモンがありません</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- PokeAPI インポート -->
        <div class="col-md-6" data-widget-id="import" x-show="isVisible('import')">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <strong><i class="bi bi-cloud-download"></i> データインポート</strong>
                    <i class="bi bi-grip-vertical text-muted" x-show="editMode" style="cursor:grab"></i>
                </div>
                <div class="card-body text-center py-4">
                    <i class="bi bi-cloud-download text-muted" style="font-size:2.5rem"></i>
                    <p class="text-muted mt-2 mb-3" style="font-size:.9rem">PokeAPIからポケモン・わざのデータを取得できます</p>
                    <a href="{{ route('master.import') }}" class="btn btn-outline-primary btn-sm">インポートページへ</a>
                </div>
            </div>
        </div>

        <!-- ダメージ計算式 -->
        <div class="col-md-6" data-widget-id="formula" x-show="isVisible('formula')">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <strong><i class="bi bi-info-circle"></i> ダメージ計算式</strong>
                    <i class="bi bi-grip-vertical text-muted" x-show="editMode" style="cursor:grab"></i>
                </div>
                <div class="card-body text-center py-4">
                    <i class="bi bi-calculator text-warning" style="font-size:2.5rem"></i>
                    <p class="text-muted mt-2 mb-3" style="font-size:.9rem">ダメージ計算式の詳細・補正の全解説</p>
                    <a href="{{ route('damage-calc.formula') }}" class="btn btn-outline-warning btn-sm">計算式の解説へ</a>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
function dashboard() {
    const STORAGE_KEY = 'pokeNote_dashboard_v1';
    const DEFAULT_WIDGETS = ['quicklinks','stats','recent-battles','my-pokemon','import','formula'];

    return {
        editMode: false,
        allWidgets: [
            {id:'quicklinks',    label:'クイックリンク'},
            {id:'stats',         label:'対戦統計'},
            {id:'recent-battles',label:'最近の対戦'},
            {id:'my-pokemon',    label:'マイポケモン'},
            {id:'import',        label:'データインポート'},
            {id:'formula',       label:'ダメージ計算式'},
        ],
        visibleWidgets: [],
        sortable: null,

        init() {
            const saved = JSON.parse(localStorage.getItem(STORAGE_KEY) || 'null');
            this.visibleWidgets = saved?.visible ?? [...DEFAULT_WIDGETS];
            const savedOrder = saved?.order ?? [...DEFAULT_WIDGETS];

            this.$nextTick(() => {
                this.applyOrder(savedOrder);
            });
        },

        isVisible(id) {
            return this.visibleWidgets.includes(id);
        },

        toggleWidget(id) {
            if (this.isVisible(id)) {
                this.visibleWidgets = this.visibleWidgets.filter(w => w !== id);
            } else {
                this.visibleWidgets.push(id);
            }
            this.saveLayout();
        },

        toggleEdit() {
            this.editMode = !this.editMode;
            if (this.editMode) {
                this.$nextTick(() => this.initSortable());
            } else {
                if (this.sortable) { this.sortable.destroy(); this.sortable = null; }
                this.saveLayout();
            }
        },

        initSortable() {
            const container = document.getElementById('widget-container');
            if (!container) return;
            if (this.sortable) this.sortable.destroy();
            this.sortable = Sortable.create(container, {
                animation: 150,
                handle: '.bi-grip-vertical',
                ghostClass: 'opacity-50',
                onEnd: () => this.saveLayout(),
            });
        },

        applyOrder(order) {
            const container = document.getElementById('widget-container');
            if (!container) return;
            order.forEach(id => {
                const el = container.querySelector(`[data-widget-id="${id}"]`);
                if (el) container.appendChild(el);
            });
        },

        saveLayout() {
            const container = document.getElementById('widget-container');
            const order = Array.from(container.querySelectorAll('[data-widget-id]'))
                .map(el => el.getAttribute('data-widget-id'));
            localStorage.setItem(STORAGE_KEY, JSON.stringify({
                visible: this.visibleWidgets,
                order,
            }));
        },
    };
}
</script>
@endpush
