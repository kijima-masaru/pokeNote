@extends('layouts.app')
@section('title', 'マイポケモン')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-star"></i> マイポケモン</h4>
    <div class="d-flex gap-2">
        <button class="btn btn-sm btn-outline-info" onclick="exportAll()" title="全ポケモンをJSONでエクスポート">
            <i class="bi bi-download"></i> エクスポート
        </button>
        <label class="btn btn-sm btn-outline-secondary mb-0" title="JSONからインポート">
            <i class="bi bi-upload"></i> インポート
            <input type="file" accept=".json,application/json" class="d-none" onchange="importFromJson(event)">
        </label>
        <a href="{{ route('custom-pokemon.bulk') }}" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-file-earmark-spreadsheet"></i> バルク登録
        </a>
        <a href="{{ route('custom-pokemon.create') }}" class="btn btn-success btn-sm">
            <i class="bi bi-plus-circle"></i> 新規登録
        </a>
    </div>
</div>
<div id="importResult" class="alert d-none mb-3"></div>
<form method="GET" action="{{ route('custom-pokemon.index') }}" class="mb-3 d-flex gap-2">
    <input type="text" name="search" class="form-control form-control-sm" style="max-width:220px"
           placeholder="名前・ニックネームで検索" value="{{ $search ?? '' }}">
    <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="bi bi-search"></i></button>
    @if($search)
        <a href="{{ route('custom-pokemon.index') }}" class="btn btn-sm btn-outline-danger"><i class="bi bi-x"></i> クリア</a>
    @endif
</form>
@if($customPokemon->isEmpty())
    <div class="text-center py-5 text-muted">
        <i class="bi bi-inbox" style="font-size:3rem"></i>
        <div class="mt-2">マイポケモンが登録されていません</div>
        <a href="{{ route('custom-pokemon.create') }}" class="btn btn-success mt-3">最初のポケモンを登録</a>
    </div>
@else
    <div class="row g-3">
        @foreach($customPokemon as $cp)
        <div class="col-md-4 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        @if($cp->pokemon->sprite_url)
                            <img src="{{ $cp->pokemon->sprite_url }}" style="width:48px;height:48px;object-fit:contain" class="me-2">
                        @else
                            <div class="bg-light rounded me-2 d-flex align-items-center justify-content-center" style="width:48px;height:48px">
                                <i class="bi bi-question text-muted"></i>
                            </div>
                        @endif
                        <div>
                            <div class="fw-bold">{{ $cp->display_name }}</div>
                            @if($cp->nickname)<small class="text-muted">{{ $cp->pokemon->name_ja }}</small>@endif
                        </div>
                    </div>
                    <div class="mb-2">
                        @foreach($cp->pokemon->types as $type)
                            <span class="type-badge type-{{ $type->type }}">{{ \App\Enums\PokemonType::from($type->type)->label() }}</span>
                        @endforeach
                        <span class="badge bg-light text-dark border ms-1">{{ \App\Enums\Nature::from($cp->nature)->label() }}</span>
                    </div>
                    @if($cp->item)
                        <div class="mb-1"><small class="text-muted"><i class="bi bi-bag"></i> {{ $cp->item->name_ja }}</small></div>
                    @endif
                    <div class="mb-2">
                        @foreach($cp->moves as $move)
                            <span class="badge bg-light text-dark border me-1 mb-1" style="font-size:.7rem">{{ $move->name_ja }}</span>
                        @endforeach
                    </div>
                    <div class="d-flex gap-1 mt-auto">
                        <a href="{{ route('custom-pokemon.show', $cp->id) }}" class="btn btn-sm btn-outline-primary flex-grow-1">詳細</a>
                        <a href="{{ route('damage-calc.index') }}?attacker={{ $cp->id }}" class="btn btn-sm btn-outline-warning" title="ダメージ計算"><i class="bi bi-calculator"></i></a>
                        <a href="{{ route('custom-pokemon.edit', $cp->id) }}" class="btn btn-sm btn-outline-secondary" title="編集"><i class="bi bi-pencil"></i></a>
                        <button class="btn btn-sm btn-outline-info" title="コピー"
                                onclick="duplicateCustomPokemon({{ $cp->id }}, this)">
                            <i class="bi bi-copy"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" title="削除"
                                onclick="deleteCustomPokemon({{ $cp->id }}, this)">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    <div class="mt-3 d-flex justify-content-between align-items-center">
        <small class="text-muted">{{ $customPokemon->total() }}件中 {{ $customPokemon->firstItem() }}〜{{ $customPokemon->lastItem() }}件表示</small>
        {{ $customPokemon->links('pagination::bootstrap-5') }}
    </div>
@endif
@endsection
@push('scripts')
<script>
async function duplicateCustomPokemon(id, btn) {
    btn.disabled = true;
    const res = await fetch(`/api/v1/custom-pokemon/${id}/duplicate`, {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content},
    });
    if (res.ok) {
        if (window.showToast) showToast('コピーを作成しました。編集画面に移動します...');
        const copy = await res.json();
        setTimeout(() => { location.href = `/custom-pokemon/${copy.id}/edit`; }, 800);
    } else {
        alert('コピーに失敗しました');
        btn.disabled = false;
    }
}

async function exportAll() {
    const res = await fetch('/api/v1/custom-pokemon/export-all', {
        headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content},
    });
    const data = await res.json();
    const blob = new Blob([JSON.stringify(data, null, 2)], {type: 'application/json'});
    const url  = URL.createObjectURL(blob);
    const a    = document.createElement('a');
    a.href     = url;
    a.download = `pokenote_export_${new Date().toISOString().slice(0,10)}.json`;
    a.click();
    URL.revokeObjectURL(url);
}

async function importFromJson(event) {
    const file = event.target.files[0];
    if (!file) return;
    const text = await file.text();
    let parsed;
    try {
        parsed = JSON.parse(text);
    } catch {
        showImportResult('danger', 'JSONの解析に失敗しました。有効なJSONファイルを選択してください。');
        event.target.value = '';
        return;
    }

    // exportAll形式（{pokemon:[...]}）と単体配列の両方に対応
    const list = Array.isArray(parsed) ? parsed
               : Array.isArray(parsed.pokemon) ? parsed.pokemon
               : [parsed];

    const res = await fetch('/api/v1/custom-pokemon/import', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({data: list}),
    });
    const result = await res.json();
    if (result.imported_count > 0) {
        let msg = `${result.imported_count}体のポケモンをインポートしました。`;
        if (result.failed_count > 0) {
            msg += ` (${result.failed_count}体は失敗: ${result.failed.map(f=>f.reason).join(', ')})`;
        }
        showImportResult('success', msg);
        setTimeout(() => location.reload(), 1500);
    } else {
        const reasons = result.failed.map(f => f.reason).join('、');
        showImportResult('danger', `インポートに失敗しました: ${reasons}`);
    }
    event.target.value = '';
}

function showImportResult(type, msg) {
    const el = document.getElementById('importResult');
    el.className = `alert alert-${type} mb-3`;
    el.textContent = msg;
}

async function deleteCustomPokemon(id, btn) {
    if (!confirm('このポケモンを削除しますか？')) return;
    btn.disabled = true;
    try {
        const res = await fetch(`/api/v1/custom-pokemon/${id}`, {
            method: 'DELETE',
            headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content},
        });
        if (res.ok) {
            btn.closest('.col-md-4').remove();
        } else {
            alert('削除に失敗しました');
            btn.disabled = false;
        }
    } catch {
        alert('削除に失敗しました');
        btn.disabled = false;
    }
}
</script>
@endpush
