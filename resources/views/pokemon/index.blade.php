@extends('layouts.app')
@section('title', 'ポケモン図鑑')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-collection"></i> ポケモン図鑑</h4>
</div>
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <input type="text" id="searchName" class="form-control form-control-sm" placeholder="名前で検索...">
            </div>
            <div class="col-md-3">
                <select id="searchType" class="form-select form-select-sm">
                    <option value="">すべてのタイプ</option>
                    @foreach($types as $type)
                        <option value="{{ $type->value }}">{{ $type->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select id="filterMega" class="form-select form-select-sm">
                    <option value="">すべて</option>
                    <option value="0">通常のみ</option>
                    <option value="1">メガシンカのみ</option>
                </select>
            </div>
            <div class="col-md-2">
                <select id="sortCol" class="form-select form-select-sm">
                    <option value="pokedex_number">図鑑番号順</option>
                    <option value="base_hp">HP順</option>
                    <option value="base_attack">攻撃順</option>
                    <option value="base_defense">防御順</option>
                    <option value="base_sp_attack">特攻順</option>
                    <option value="base_sp_defense">特防順</option>
                    <option value="base_speed">素早さ順</option>
                </select>
            </div>
            <div class="col-md-1">
                <select id="sortDir" class="form-select form-select-sm">
                    <option value="asc">昇順</option>
                    <option value="desc" selected>降順</option>
                </select>
            </div>
        </div>
    </div>
</div>
<div id="pokemonGrid" class="row g-2"></div>
<div id="pagination" class="d-flex justify-content-center mt-3 gap-2"></div>
@endsection
@push('scripts')
<script>
let currentPage = 1;
const searchName = document.getElementById('searchName');
const searchType = document.getElementById('searchType');
const filterMega = document.getElementById('filterMega');

const typeLabels = {
    normal:'ノーマル',fire:'ほのお',water:'みず',electric:'でんき',grass:'くさ',
    ice:'こおり',fighting:'かくとう',poison:'どく',ground:'じめん',flying:'ひこう',
    psychic:'エスパー',bug:'むし',rock:'いわ',ghost:'ゴースト',dragon:'ドラゴン',
    dark:'あく',steel:'はがね',fairy:'フェアリー'
};

async function fetchPokemon(page = 1) {
    const params = new URLSearchParams({ page, per_page: 40 });
    const name = searchName.value.trim();
    const type = searchType.value;
    if (name) params.set('name', name);
    if (type) params.set('type', type);
    if (filterMega.value !== '') params.set('is_mega', filterMega.value);

    const res = await fetch(`/api/v1/pokemon?${params}`);
    const data = await res.json();
    renderGrid(data.data);
    renderPagination(data);
}

function renderGrid(pokemon) {
    const grid = document.getElementById('pokemonGrid');
    if (!pokemon || pokemon.length === 0) {
        grid.innerHTML = '<div class="col-12 text-center text-muted py-5">ポケモンが見つかりません</div>';
        return;
    }
    grid.innerHTML = pokemon.map(p => `
        <div class="col-6 col-md-3 col-lg-2">
            <a href="/pokemon/${p.id}" class="text-decoration-none text-dark">
                <div class="card border-0 shadow-sm pokemon-card p-2 text-center h-100">
                    <div style="height:64px;display:flex;align-items:center;justify-content:center">
                        ${p.sprite_url
                            ? `<img src="${p.sprite_url}" style="max-height:64px;max-width:64px">`
                            : `<i class="bi bi-question-circle text-muted" style="font-size:2rem"></i>`}
                    </div>
                    <div style="font-size:.65rem;color:#6c757d">#${String(p.pokedex_number).padStart(4,'0')}${p.is_mega ? ' <span class="badge bg-warning text-dark" style="font-size:.6rem">メガ</span>' : ''}</div>
                    <div class="fw-semibold" style="font-size:.85rem">${p.name_ja}</div>
                    <div class="mt-1">
                        ${(p.types||[]).map(t=>`<span class="type-badge type-${t.type}">${typeLabels[t.type]||t.type}</span>`).join(' ')}
                    </div>
                    <div class="mt-1" style="font-size:.7rem;color:#6c757d">合計: ${p.base_total}</div>
                </div>
            </a>
        </div>
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

function goPage(page) { currentPage = page; fetchPokemon(page); }

let debounceTimer;
searchName.addEventListener('input', () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => { currentPage = 1; fetchPokemon(1); }, 400);
});
searchType.addEventListener('change', () => { currentPage = 1; fetchPokemon(1); });
filterMega.addEventListener('change', () => { currentPage = 1; fetchPokemon(1); });

fetchPokemon(1);
</script>
@endpush
