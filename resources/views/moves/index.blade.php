@extends('layouts.app')
@section('title', 'わざ一覧')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-lightning-charge"></i> わざ一覧</h4>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <input type="text" id="searchName" class="form-control form-control-sm" placeholder="わざ名で検索...">
            </div>
            <div class="col-md-3">
                <select id="searchType" class="form-select form-select-sm">
                    <option value="">すべてのタイプ</option>
                    @foreach(\App\Enums\PokemonType::cases() as $type)
                        <option value="{{ $type->value }}">{{ $type->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select id="searchCategory" class="form-select form-select-sm">
                    <option value="">すべての分類</option>
                    <option value="physical">物理</option>
                    <option value="special">特殊</option>
                    <option value="status">変化</option>
                </select>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>わざ名</th>
                    <th>タイプ</th>
                    <th>分類</th>
                    <th class="text-center">威力</th>
                    <th class="text-center">命中</th>
                    <th class="text-center">PP</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="moveTableBody">
                <tr><td colspan="7" class="text-center text-muted py-4">読み込み中...</td></tr>
            </tbody>
        </table>
    </div>
</div>
<div id="pagination" class="d-flex justify-content-center mt-3 gap-2"></div>
@endsection
@push('scripts')
<script>
const TYPE_LABELS = {
    normal:'ノーマル',fire:'ほのお',water:'みず',electric:'でんき',grass:'くさ',
    ice:'こおり',fighting:'かくとう',poison:'どく',ground:'じめん',flying:'ひこう',
    psychic:'エスパー',bug:'むし',rock:'いわ',ghost:'ゴースト',dragon:'ドラゴン',
    dark:'あく',steel:'はがね',fairy:'フェアリー'
};
const CAT_LABELS = {physical:'物理', special:'特殊', status:'変化'};
const CAT_COLORS = {physical:'danger', special:'primary', status:'secondary'};

let currentPage = 1;
const searchName     = document.getElementById('searchName');
const searchType     = document.getElementById('searchType');
const searchCategory = document.getElementById('searchCategory');

async function fetchMoves(page = 1) {
    const params = new URLSearchParams({page, per_page: 30});
    const name = searchName.value.trim();
    const type = searchType.value;
    const cat  = searchCategory.value;
    if (name) params.set('name', name);
    if (type) params.set('type', type);
    if (cat)  params.set('category', cat);

    const res = await fetch(`/api/v1/moves?${params}`);
    const data = await res.json();
    renderTable(data.data);
    renderPagination(data);
}

function renderTable(moves) {
    const tbody = document.getElementById('moveTableBody');
    if (!moves || moves.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">わざが見つかりません</td></tr>';
        return;
    }
    tbody.innerHTML = moves.map(m => `
        <tr style="cursor:pointer" onclick="location.href='/moves/${m.id}'">
            <td class="fw-semibold">${m.name_ja}</td>
            <td><span class="type-badge type-${m.type}">${TYPE_LABELS[m.type]||m.type}</span></td>
            <td><span class="badge bg-${CAT_COLORS[m.category]||'secondary'}">${CAT_LABELS[m.category]||m.category}</span></td>
            <td class="text-center">${m.power ?? '-'}</td>
            <td class="text-center">${m.accuracy ?? '-'}</td>
            <td class="text-center">${m.pp ?? '-'}</td>
            <td>
                <a href="/moves/${m.id}" class="btn btn-sm btn-outline-secondary" onclick="event.stopPropagation()">詳細</a>
            </td>
        </tr>
    `).join('');
}

function renderPagination(data) {
    const pag = document.getElementById('pagination');
    if (data.last_page <= 1) { pag.innerHTML = ''; return; }
    let html = '';
    if (data.current_page > 1)
        html += `<button class="btn btn-sm btn-outline-secondary" onclick="goPage(${data.current_page-1})">前へ</button>`;
    html += `<span class="btn btn-sm btn-secondary disabled">${data.current_page} / ${data.last_page}</span>`;
    if (data.current_page < data.last_page)
        html += `<button class="btn btn-sm btn-outline-secondary" onclick="goPage(${data.current_page+1})">次へ</button>`;
    pag.innerHTML = html;
}

function goPage(page) { currentPage = page; fetchMoves(page); }

let debounceTimer;
searchName.addEventListener('input', () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => { currentPage = 1; fetchMoves(1); }, 350);
});
searchType.addEventListener('change',     () => { currentPage = 1; fetchMoves(1); });
searchCategory.addEventListener('change', () => { currentPage = 1; fetchMoves(1); });

fetchMoves(1);
</script>
@endpush
