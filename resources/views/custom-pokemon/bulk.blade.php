@extends('layouts.app')
@section('title', 'バルク登録')
@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('custom-pokemon.index') }}">マイポケモン</a></li>
        <li class="breadcrumb-item active">バルク登録</li>
    </ol>
</nav>

<div class="row g-3">
    <!-- CSV インポート -->
    <div class="col-md-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <strong><i class="bi bi-file-earmark-spreadsheet"></i> CSVからバルク登録</strong>
            </div>
            <div class="card-body">
                <p class="text-muted" style="font-size:.9rem">
                    CSV形式でポケモンを一括登録できます。ヘッダー行は必須です。<br>
                    ポケモン名・特性名・技名は <strong>英語名 または 日本語名</strong> で入力してください。
                </p>

                <div id="csvResult" class="alert d-none mb-3"></div>

                <form id="csvForm">
                    <div class="mb-3">
                        <label class="form-label">CSVファイルを選択</label>
                        <input type="file" class="form-control" id="csvFile" accept=".csv,text/csv">
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-primary" onclick="uploadCsv()">
                            <i class="bi bi-upload"></i> インポート
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="downloadTemplate()">
                            <i class="bi bi-download"></i> テンプレートDL
                        </button>
                    </div>
                </form>

                <!-- プレビュー -->
                <div id="csvPreview" class="mt-3 d-none">
                    <div class="fw-bold mb-1" style="font-size:.85rem">プレビュー（先頭5行）</div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered" id="previewTable" style="font-size:.75rem"></table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CSV フォーマット説明 -->
    <div class="col-md-5">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <strong><i class="bi bi-info-circle"></i> CSVフォーマット</strong>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0" style="font-size:.75rem">
                        <thead class="table-dark">
                            <tr><th>列名</th><th>内容</th><th>必須</th></tr>
                        </thead>
                        <tbody>
                            <tr><td>pokemon_en</td><td>ポケモン名（英/日）</td><td><span class="badge bg-danger">必須</span></td></tr>
                            <tr><td>nickname</td><td>ニックネーム</td><td></td></tr>
                            <tr><td>nature</td><td>性格（英語値）例: timid</td><td><span class="badge bg-danger">必須</span></td></tr>
                            <tr><td>ability_en</td><td>特性名（英/日）</td><td><span class="badge bg-danger">必須</span></td></tr>
                            <tr><td>item_en</td><td>持ち物名（英/日）</td><td></td></tr>
                            <tr><td>level</td><td>レベル 1-100</td><td></td></tr>
                            <tr><td>iv_hp〜iv_spe</td><td>個体値 HP/atk/def/spa/spd/spe</td><td></td></tr>
                            <tr><td>ev_hp〜ev_spe</td><td>努力値 HP/atk/def/spa/spd/spe</td><td></td></tr>
                            <tr><td>move1〜move4</td><td>技名（英/日）</td><td></td></tr>
                            <tr><td>memo</td><td>メモ</td><td></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <strong><i class="bi bi-lightbulb"></i> 性格値一覧</strong>
            </div>
            <div class="card-body p-2">
                <div class="d-flex flex-wrap gap-1" style="font-size:.72rem">
                    @foreach(\App\Enums\Nature::cases() as $n)
                    <span class="badge bg-light text-dark border">{{ $n->value }} = {{ $n->label() }}</span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
const TEMPLATE_HEADER = 'pokemon_en,nickname,nature,ability_en,item_en,level,iv_hp,iv_atk,iv_def,iv_spa,iv_spd,iv_spe,ev_hp,ev_atk,ev_def,ev_spa,ev_spd,ev_spe,move1,move2,move3,move4,memo';
const TEMPLATE_EXAMPLE = 'Charizard,カイリュー,timid,Blaze,,50,31,31,31,31,31,31,0,0,0,252,4,252,Flamethrower,Air Slash,Dragon Pulse,Roost,例文';

function downloadTemplate() {
    const csv = TEMPLATE_HEADER + '\n' + TEMPLATE_EXAMPLE + '\n';
    const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
    const url  = URL.createObjectURL(blob);
    const a    = document.createElement('a');
    a.href     = url;
    a.download = 'pokenote_bulk_template.csv';
    a.click();
    URL.revokeObjectURL(url);
}

document.getElementById('csvFile').addEventListener('change', previewCsv);

function previewCsv() {
    const file = document.getElementById('csvFile').files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        const lines = e.target.result.split('\n').filter(l => l.trim());
        const preview = lines.slice(0, 6); // ヘッダー + 5行
        const table   = document.getElementById('previewTable');
        table.innerHTML = preview.map((line, i) => {
            const cols = line.split(',').map(c => `<td>${c.trim()}</td>`).join('');
            return i === 0 ? `<thead class="table-dark"><tr>${cols}</tr></thead>` : `<tr>${cols}</tr>`;
        }).join('');
        document.getElementById('csvPreview').classList.remove('d-none');
    };
    reader.readAsText(file, 'UTF-8');
}

async function uploadCsv() {
    const file = document.getElementById('csvFile').files[0];
    if (!file) { showResult('warning', 'CSVファイルを選択してください'); return; }

    const formData = new FormData();
    formData.append('file', file);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

    try {
        const res = await fetch('/api/v1/custom-pokemon/import-csv', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: formData,
        });
        const data = await res.json();

        if (data.imported_count > 0) {
            let msg = `✅ ${data.imported_count}件を登録しました。`;
            if (data.failed_count > 0) {
                msg += `<br>⚠️ ${data.failed_count}件の失敗:<br>`;
                msg += data.failed.map(f => `行${f.row}: ${f.reason}`).join('<br>');
            }
            showResult('success', msg);
            if (window.showToast) showToast(`${data.imported_count}件を登録しました`);
        } else {
            const errors = (data.failed || []).map(f => `行${f.row}: ${f.reason}`).join('<br>');
            showResult('danger', '登録に失敗しました。<br>' + (errors || data.message || ''));
        }
    } catch (e) {
        showResult('danger', 'エラーが発生しました: ' + e.message);
    }
}

function showResult(type, html) {
    const el = document.getElementById('csvResult');
    el.className = `alert alert-${type}`;
    el.innerHTML = html;
    el.classList.remove('d-none');
    el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}
</script>
@endpush
