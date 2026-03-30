@extends('layouts.app')
@section('title', $move->name_ja)
@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('moves.index') }}">わざ一覧</a></li>
        <li class="breadcrumb-item active">{{ $move->name_ja }}</li>
    </ol>
</nav>

<div class="row g-3">
    <!-- 左: わざ詳細 -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body text-center py-4">
                <h3 class="mb-1">{{ $move->name_ja }}</h3>
                <div class="text-muted mb-3">{{ $move->name_en }}</div>
                <div class="mb-3">
                    <span class="type-badge type-{{ $move->type }} me-1" style="font-size:.9rem">
                        {{ \App\Enums\PokemonType::from($move->type)->label() }}
                    </span>
                    @php $catLabel = ['physical'=>'物理','special'=>'特殊','status'=>'変化']; @endphp
                    @php $catColor = ['physical'=>'danger','special'=>'primary','status'=>'secondary']; @endphp
                    <span class="badge bg-{{ $catColor[$move->category] ?? 'secondary' }}" style="font-size:.85rem">
                        {{ $catLabel[$move->category] ?? $move->category }}
                    </span>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white"><strong>基本情報</strong></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tbody>
                        <tr>
                            <th class="text-muted ps-3" style="width:40%">威力</th>
                            <td class="fw-bold">{{ $move->power ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted ps-3">命中率</th>
                            <td class="fw-bold">{{ $move->accuracy ? $move->accuracy.'%' : '—' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted ps-3">PP</th>
                            <td class="fw-bold">{{ $move->pp ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted ps-3">優先度</th>
                            <td class="fw-bold">
                                {{ $move->priority ?? 0 }}
                                @if(($move->priority ?? 0) > 0)
                                    <span class="badge bg-warning text-dark ms-1">先制</span>
                                @elseif(($move->priority ?? 0) < 0)
                                    <span class="badge bg-secondary ms-1">後攻</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted ps-3">接触</th>
                            <td>
                                @if($move->makes_contact)
                                    <span class="badge bg-info text-dark">接触あり</span>
                                @else
                                    <span class="badge bg-light text-muted">接触なし</span>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        @if($move->description)
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white"><strong>説明</strong></div>
            <div class="card-body text-muted" style="font-size:.9rem;line-height:1.7">
                {{ $move->description }}
            </div>
        </div>
        @endif
    </div>

    <!-- 右: このわざを覚えるポケモン -->
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong>このわざを覚えるポケモン</strong>
                <span class="badge bg-secondary">{{ $move->pokemon->count() }}体</span>
            </div>
            @if($move->pokemon->isEmpty())
                <div class="card-body text-center text-muted py-4">
                    このわざを覚えるポケモンはまだ登録されていません
                </div>
            @else
                <div class="card-body">
                    <div class="row g-2">
                        @foreach($move->pokemon->sortBy('pokedex_number') as $p)
                        <div class="col-6 col-md-3 col-lg-2">
                            <a href="{{ route('pokemon.show', $p->id) }}" class="text-decoration-none text-dark">
                                <div class="card border-0 shadow-sm pokemon-card p-2 text-center h-100">
                                    @if($p->sprite_url)
                                        <img src="{{ $p->sprite_url }}" style="height:56px;object-fit:contain;margin:0 auto">
                                    @else
                                        <div class="text-muted py-2"><i class="bi bi-question-circle" style="font-size:1.8rem"></i></div>
                                    @endif
                                    <div style="font-size:.65rem;color:#6c757d">
                                        #{{ str_pad($p->pokedex_number, 4, '0', STR_PAD_LEFT) }}
                                    </div>
                                    <div class="fw-semibold" style="font-size:.8rem">{{ $p->name_ja }}</div>
                                    <div class="mt-1">
                                        @foreach($p->types as $t)
                                            <span class="type-badge type-{{ $t->type }}" style="font-size:.6rem">
                                                {{ \App\Enums\PokemonType::from($t->type)->label() }}
                                            </span>
                                        @endforeach
                                    </div>
                                    @php
                                        $learnMethod = $p->pivot->learn_method ?? 'level-up';
                                        $methodLabels = [
                                            'level-up' => ['label'=>'Lv', 'class'=>'bg-success'],
                                            'machine'  => ['label'=>'マシン', 'class'=>'bg-primary'],
                                            'egg'      => ['label'=>'タマゴ', 'class'=>'bg-warning text-dark'],
                                            'tutor'    => ['label'=>'教え', 'class'=>'bg-info text-dark'],
                                        ];
                                        $m = $methodLabels[$learnMethod] ?? ['label'=>$learnMethod, 'class'=>'bg-secondary'];
                                    @endphp
                                    <div class="mt-1">
                                        <span class="badge {{ $m['class'] }}" style="font-size:.58rem">
                                            {{ $m['label'] }}{{ ($learnMethod==='level-up' && $p->pivot->level_learned) ? ' '.$p->pivot->level_learned : '' }}
                                        </span>
                                    </div>
                                </div>
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
