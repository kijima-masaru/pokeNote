@extends('layouts.app')
@section('title', $cp->display_name)
@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('custom-pokemon.index') }}">マイポケモン</a></li>
        <li class="breadcrumb-item active">{{ $cp->display_name }}</li>
    </ol>
</nav>
<div class="row g-3">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center p-4">
            @if($cp->pokemon->sprite_url)
                <img src="{{ $cp->pokemon->sprite_url }}" alt="" style="max-height:120px;margin:0 auto">
            @endif
            <h4 class="mt-2 mb-0">{{ $cp->display_name }}</h4>
            @if($cp->nickname)<div class="text-muted">{{ $cp->pokemon->name_ja }}</div>@endif
            <div class="my-2">
                @foreach($cp->pokemon->types as $type)
                    <span class="type-badge type-{{ $type->type }}">{{ \App\Enums\PokemonType::from($type->type)->label() }}</span>
                @endforeach
            </div>
            <table class="table table-sm text-start">
                <tr><th>性格</th><td>{{ \App\Enums\Nature::from($cp->nature)->label() }}</td></tr>
                <tr><th>特性</th><td>{{ $cp->ability->name_ja }}</td></tr>
                <tr><th>持ち物</th><td>{{ $cp->item?->name_ja ?? 'なし' }}</td></tr>
                <tr><th>レベル</th><td>{{ $cp->level }}</td></tr>
            </table>
            @if($cp->memo)<div class="alert alert-light text-start" style="font-size:.85rem">{{ $cp->memo }}</div>@endif
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('custom-pokemon.edit', $cp->id) }}" class="btn btn-sm btn-outline-secondary flex-grow-1">
                    <i class="bi bi-pencil"></i> 編集
                </a>
                <button class="btn btn-sm btn-outline-info flex-grow-1" onclick="duplicateThis()">
                    <i class="bi bi-copy"></i> コピー
                </button>
                <a href="{{ route('damage-calc.index') }}?attacker={{ $cp->id }}" class="btn btn-sm btn-outline-warning flex-grow-1">
                    <i class="bi bi-calculator"></i> ダメ計
                </a>
                <button class="btn btn-sm btn-outline-success flex-grow-1" data-bs-toggle="modal" data-bs-target="#qrModal">
                    <i class="bi bi-qr-code"></i> QR
                </button>
                <button class="btn btn-sm btn-outline-danger" onclick="deleteThis()">
                    <i class="bi bi-trash"></i>
                </button>
            </div>

            <!-- QRコードモーダル -->
            <div class="modal fade" id="qrModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h6 class="modal-title"><i class="bi bi-qr-code"></i> QRコードで共有</h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-center">
                            <p class="text-muted mb-3" style="font-size:.85rem">このQRコードをスキャンするとポケモン構成JSONをインポートできます。</p>
                            <div id="qrcode" class="d-flex justify-content-center mb-3"></div>
                            <button class="btn btn-sm btn-outline-secondary" onclick="downloadQr()">
                                <i class="bi bi-download"></i> PNG保存
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong>実数値</strong>
                <small class="text-muted">種族値との比較</small>
            </div>
            <div class="card-body">
                @php
                    $stats   = $cp->actual_stats;
                    $baseMap = ['hp'=>$cp->pokemon->hp,'attack'=>$cp->pokemon->attack,'defense'=>$cp->pokemon->defense,
                                'sp_attack'=>$cp->pokemon->sp_attack,'sp_defense'=>$cp->pokemon->sp_defense,'speed'=>$cp->pokemon->speed];
                    $ivs  = ['hp'=>$cp->iv_hp,'attack'=>$cp->iv_attack,'defense'=>$cp->iv_defense,
                             'sp_attack'=>$cp->iv_sp_attack,'sp_defense'=>$cp->iv_sp_defense,'speed'=>$cp->iv_speed];
                    $evs  = ['hp'=>$cp->ev_hp,'attack'=>$cp->ev_attack,'defense'=>$cp->ev_defense,
                             'sp_attack'=>$cp->ev_sp_attack,'sp_defense'=>$cp->ev_sp_defense,'speed'=>$cp->ev_speed];
                    $labels = ['hp'=>'HP','attack'=>'攻撃','defense'=>'防御','sp_attack'=>'特攻','sp_defense'=>'特防','speed'=>'素早さ'];
                    // 実数値の色分け: <80赤 <100橙 <120黄 <150緑 else青
                    $statColor = function(int $v): string {
                        if ($v < 80)  return '#dc3545';
                        if ($v < 100) return '#fd7e14';
                        if ($v < 120) return '#ffc107';
                        if ($v < 150) return '#198754';
                        return '#0d6efd';
                    };
                @endphp
                @foreach($labels as $key => $label)
                @php
                    $actual = $stats[$key] ?? 0;
                    $base   = $baseMap[$key] ?? 0;
                    $barPct = min(100, $actual / 255 * 100);
                    $color  = $statColor($actual);
                @endphp
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-muted" style="width:55px;font-size:.8rem">{{ $label }}</span>
                        <span class="fw-bold" style="font-size:1.05rem;color:{{ $color }}">{{ $actual }}</span>
                        <span class="text-muted ms-auto" style="font-size:.75rem">種族値 {{ $base }} &nbsp;|&nbsp; IV {{ $ivs[$key] }} / EV {{ $evs[$key] }}</span>
                    </div>
                    <div style="height:10px;background:#e9ecef;border-radius:5px;overflow:hidden">
                        <div style="height:100%;border-radius:5px;width:{{ $barPct }}%;background:{{ $color }};transition:width .4s"></div>
                    </div>
                </div>
                @endforeach
                <div class="text-end mt-1" style="font-size:.8rem;color:#6c757d">
                    合計実数値: <strong>{{ array_sum($stats) }}</strong>
                </div>
            </div>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><strong>技構成</strong></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:2rem">#</th>
                            <th>技名</th>
                            <th>タイプ</th>
                            <th>分類</th>
                            <th class="text-center">威力</th>
                            <th class="text-center">命中</th>
                            <th class="text-center">PP</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cp->moves as $move)
                        <tr>
                            <td class="text-muted">{{ $move->pivot->slot }}</td>
                            <td class="fw-semibold">
                                <a href="{{ route('moves.show', $move->id) }}" class="text-decoration-none">
                                    {{ $move->name_ja }}
                                </a>
                            </td>
                            <td><span class="type-badge type-{{ $move->type }}" style="font-size:.7rem">{{ \App\Enums\PokemonType::from($move->type)->label() }}</span></td>
                            <td>
                                @if($move->category==='physical')<span class="badge bg-danger" style="font-size:.7rem">物理</span>
                                @elseif($move->category==='special')<span class="badge bg-primary" style="font-size:.7rem">特殊</span>
                                @else<span class="badge bg-secondary" style="font-size:.7rem">変化</span>@endif
                            </td>
                            <td class="text-center">{{ $move->power ?? '-' }}</td>
                            <td class="text-center">{{ $move->accuracy ?? '-' }}</td>
                            <td class="text-center">{{ $move->pp ?? '-' }}</td>
                        </tr>
                        @if($move->description)
                        <tr class="table-light">
                            <td></td>
                            <td colspan="6" style="font-size:.78rem;color:#6c757d;padding-top:2px;padding-bottom:6px">
                                {{ $move->description }}
                            </td>
                        </tr>
                        @endif
                        @endforeach
                        @for($s=$cp->moves->count();$s<4;$s++)
                        <tr><td class="text-muted">{{ $s+1 }}</td><td colspan="6" class="text-muted">-</td></tr>
                        @endfor
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
// QRコード生成
const cpJson = @json([
    'pokemon_en'  => $cp->pokemon->name_en,
    'nature'      => $cp->nature,
    'ability_en'  => $cp->ability->name_en ?? null,
    'item_en'     => $cp->item?->name_en ?? null,
    'level'       => $cp->level,
    'nickname'    => $cp->nickname,
    'ivs'         => ['hp'=>$cp->iv_hp,'attack'=>$cp->iv_attack,'defense'=>$cp->iv_defense,'sp_attack'=>$cp->iv_sp_attack,'sp_defense'=>$cp->iv_sp_defense,'speed'=>$cp->iv_speed],
    'evs'         => ['hp'=>$cp->ev_hp,'attack'=>$cp->ev_attack,'defense'=>$cp->ev_defense,'sp_attack'=>$cp->ev_sp_attack,'sp_defense'=>$cp->ev_sp_defense,'speed'=>$cp->ev_speed],
    'moves_en'    => $cp->moves->pluck('name_en')->values(),
    'memo'        => $cp->memo,
]);

let qrInstance = null;
document.getElementById('qrModal').addEventListener('shown.bs.modal', function() {
    const container = document.getElementById('qrcode');
    if (!qrInstance) {
        container.innerHTML = '';
        qrInstance = new QRCode(container, {
            text: JSON.stringify(cpJson),
            width: 220,
            height: 220,
            correctLevel: QRCode.CorrectLevel.M,
        });
    }
});

function downloadQr() {
    const canvas = document.querySelector('#qrcode canvas');
    if (!canvas) return;
    const a = document.createElement('a');
    a.href = canvas.toDataURL('image/png');
    a.download = '{{ $cp->display_name }}_qr.png';
    a.click();
}

async function duplicateThis() {
    const res = await fetch('/api/v1/custom-pokemon/{{ $cp->id }}/duplicate', {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content},
    });
    if (res.ok) {
        const copy = await res.json();
        if (window.showToast) showToast('コピーを作成しました');
        setTimeout(() => { location.href = '/custom-pokemon/' + copy.id + '/edit'; }, 800);
    } else {
        alert('コピーに失敗しました');
    }
}

async function deleteThis() {
    if (!confirm('{{ $cp->display_name }} を削除しますか？')) return;
    const res = await fetch('/api/v1/custom-pokemon/{{ $cp->id }}', {
        method: 'DELETE',
        headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content},
    });
    if (res.ok) {
        location.href = '{{ route('custom-pokemon.index') }}';
    } else {
        alert('削除に失敗しました');
    }
}
</script>
@endpush
