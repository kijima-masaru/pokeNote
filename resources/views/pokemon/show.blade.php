@extends('layouts.app')
@section('title', $pokemon->name_ja)
@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('pokemon.index') }}">図鑑</a></li>
        <li class="breadcrumb-item active">{{ $pokemon->name_ja }}</li>
    </ol>
</nav>
<div class="row g-3">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center p-4">
            @if($pokemon->sprite_url)
                <img src="{{ $pokemon->sprite_url }}" alt="{{ $pokemon->name_ja }}" style="max-height:120px;margin:0 auto">
            @else
                <div class="text-muted py-4"><i class="bi bi-question-circle" style="font-size:4rem"></i></div>
            @endif
            <h4 class="mt-2 mb-0">{{ $pokemon->name_ja }}</h4>
            <div class="text-muted mb-2">#{{ str_pad($pokemon->pokedex_number, 4, '0', STR_PAD_LEFT) }} {{ $pokemon->name_en }}</div>
            @if($pokemon->form_name)
                <div class="badge bg-secondary mb-2">{{ $pokemon->form_name }}</div>
            @endif
            <div class="mb-3">
                @foreach($pokemon->types as $type)
                    <span class="type-badge type-{{ $type->type }} me-1">{{ \App\Enums\PokemonType::from($type->type)->label() }}</span>
                @endforeach
            </div>
            <div class="mb-3">
                <div class="fw-semibold mb-1">特性</div>
                @foreach($pokemon->abilities as $ability)
                    <span class="badge bg-light text-dark border me-1" title="{{ $ability->description }}">
                        {{ $ability->name_ja }}
                        @if($ability->pivot->slot == 3)<small class="text-muted">（夢）</small>@endif
                    </span>
                @endforeach
            </div>
            <a href="{{ route('custom-pokemon.create') }}?pokemon_id={{ $pokemon->id }}" class="btn btn-success btn-sm">
                <i class="bi bi-plus-circle"></i> このポケモンで登録
            </a>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <strong>種族値</strong> <span class="text-muted">(合計: {{ $pokemon->base_total }})</span>
            </div>
            <div class="card-body">
                @php
                    $stats = ['HP'=>$pokemon->base_hp,'攻撃'=>$pokemon->base_attack,'防御'=>$pokemon->base_defense,
                              '特攻'=>$pokemon->base_sp_attack,'特防'=>$pokemon->base_sp_defense,'素早さ'=>$pokemon->base_speed];
                    $colors = ['#FF6B6B','#FF9F43','#C7ECEE','#778CA3','#7EFFF5','#EEE5E9'];
                    $i = 0;
                @endphp
                @foreach($stats as $label => $value)
                <div class="d-flex align-items-center mb-2">
                    <div style="width:60px;font-size:.8rem;text-align:right" class="me-2 text-muted">{{ $label }}</div>
                    <div style="width:40px;font-size:.85rem;font-weight:600" class="me-2">{{ $value }}</div>
                    <div class="flex-grow-1 stat-bar">
                        <div class="stat-bar-fill" style="width:{{ min(100, $value/255*100) }}%;background:{{ $colors[$i] }}"></div>
                    </div>
                </div>
                @php $i++; @endphp
                @endforeach
            </div>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <strong>覚えるわざ</strong> <span class="badge bg-secondary">{{ $pokemon->moves->count() }}</span>
            </div>
            <div class="card-body p-0" style="max-height:400px;overflow-y:auto">
                @php
                    $learnMethodLabels = [
                        'level-up' => ['label' => 'レベル', 'class' => 'bg-success'],
                        'machine'  => ['label' => 'わざマシン', 'class' => 'bg-primary'],
                        'egg'      => ['label' => 'タマゴ', 'class' => 'bg-warning text-dark'],
                        'tutor'    => ['label' => '教え', 'class' => 'bg-info text-dark'],
                    ];
                    $sortedMoves = $pokemon->moves->sortBy(fn($m) => [
                        array_search($m->pivot->learn_method, ['level-up','machine','egg','tutor']),
                        $m->pivot->level_learned ?? 999,
                        $m->name_ja,
                    ]);
                @endphp
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light sticky-top">
                        <tr><th>わざ名</th><th>覚え方</th><th>タイプ</th><th>分類</th><th>威力</th><th>命中</th><th>PP</th></tr>
                    </thead>
                    <tbody>
                        @foreach($sortedMoves as $move)
                        @php
                            $method = $move->pivot->learn_method ?? 'level-up';
                            $meta = $learnMethodLabels[$method] ?? ['label' => $method, 'class' => 'bg-secondary'];
                        @endphp
                        <tr>
                            <td><a href="{{ route('moves.show', $move->id) }}" class="text-decoration-none fw-semibold">{{ $move->name_ja }}</a></td>
                            <td>
                                <span class="badge {{ $meta['class'] }}" style="font-size:.65rem">{{ $meta['label'] }}</span>
                                @if($method === 'level-up' && $move->pivot->level_learned)
                                    <span class="text-muted" style="font-size:.75rem">Lv.{{ $move->pivot->level_learned }}</span>
                                @endif
                            </td>
                            <td><span class="type-badge type-{{ $move->type }}" style="font-size:.7rem">{{ \App\Enums\PokemonType::from($move->type)->label() }}</span></td>
                            <td>
                                @if($move->category==='physical')<span class="badge bg-danger" style="font-size:.7rem">物理</span>
                                @elseif($move->category==='special')<span class="badge bg-primary" style="font-size:.7rem">特殊</span>
                                @else<span class="badge bg-secondary" style="font-size:.7rem">変化</span>@endif
                            </td>
                            <td>{{ $move->power ?? '-' }}</td>
                            <td>{{ $move->accuracy ?? '必中' }}</td>
                            <td>{{ $move->pp }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
