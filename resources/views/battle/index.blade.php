@extends('layouts.app')
@section('title', '対戦履歴')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-trophy"></i> 対戦履歴</h4>
    <a href="{{ route('battles.create') }}" class="btn btn-success btn-sm">
        <i class="bi bi-plus-circle"></i> 新規対戦
    </a>
</div>

<!-- フィルタ -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('battles.index') }}" class="row g-2 align-items-end">
            <div class="col-sm-3">
                <label class="form-label mb-1" style="font-size:.8rem">相手名</label>
                <input type="text" name="opponent" class="form-control form-control-sm"
                       placeholder="相手名で検索" value="{{ request('opponent') }}">
            </div>
            <div class="col-sm-2">
                <label class="form-label mb-1" style="font-size:.8rem">結果</label>
                <select name="result" class="form-select form-select-sm">
                    <option value="">すべて</option>
                    <option value="win"  {{ request('result') === 'win'  ? 'selected' : '' }}>勝ち</option>
                    <option value="lose" {{ request('result') === 'lose' ? 'selected' : '' }}>負け</option>
                    <option value="draw" {{ request('result') === 'draw' ? 'selected' : '' }}>引き分け</option>
                </select>
            </div>
            <div class="col-sm-2">
                <label class="form-label mb-1" style="font-size:.8rem">フォーマット</label>
                <input type="text" name="format" class="form-control form-control-sm"
                       placeholder="ランクなど" value="{{ request('format') }}">
            </div>
            <div class="col-sm-2">
                <label class="form-label mb-1" style="font-size:.8rem">日付（開始）</label>
                <input type="date" name="date_from" class="form-control form-control-sm"
                       value="{{ request('date_from') }}">
            </div>
            <div class="col-sm-2">
                <label class="form-label mb-1" style="font-size:.8rem">日付（終了）</label>
                <input type="date" name="date_to" class="form-control form-control-sm"
                       value="{{ request('date_to') }}">
            </div>
            <div class="col-sm-1 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm flex-fill">
                    <i class="bi bi-search"></i>
                </button>
                @if(request()->hasAny(['opponent','result','format','date_from','date_to']))
                <a href="{{ route('battles.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-x"></i>
                </a>
                @endif
            </div>
        </form>
    </div>
</div>

@if($opponentStats->isNotEmpty())
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <strong><i class="bi bi-person-lines-fill"></i> 対戦相手別勝率</strong>
        <small class="text-muted">相手名あり 上位{{ $opponentStats->count() }}件</small>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>相手名</th>
                        <th class="text-center">対戦数</th>
                        <th class="text-center"><span class="text-success">勝</span></th>
                        <th class="text-center"><span class="text-danger">負</span></th>
                        <th class="text-center"><span class="text-secondary">分</span></th>
                        <th style="min-width:160px">勝率</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($opponentStats as $stat)
                    <tr>
                        <td class="fw-semibold">{{ $stat->opponent_name }}</td>
                        <td class="text-center">{{ $stat->total }}</td>
                        <td class="text-center text-success fw-bold">{{ $stat->wins }}</td>
                        <td class="text-center text-danger fw-bold">{{ $stat->loses }}</td>
                        <td class="text-center text-secondary">{{ $stat->draws }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="flex-grow-1" style="height:10px;background:#e9ecef;border-radius:5px;overflow:hidden">
                                    <div style="height:100%;border-radius:5px;width:{{ $stat->win_rate }}%;background:{{ $stat->win_rate >= 60 ? '#198754' : ($stat->win_rate >= 40 ? '#ffc107' : '#dc3545') }}"></div>
                                </div>
                                <span class="fw-bold" style="width:44px;font-size:.85rem;
                                    color:{{ $stat->win_rate >= 60 ? '#198754' : ($stat->win_rate >= 40 ? '#856404' : '#dc3545') }}">
                                    {{ $stat->win_rate }}%
                                </span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@if($battles->isEmpty())
    <div class="text-center py-5 text-muted">
        <i class="bi bi-trophy" style="font-size:3rem"></i>
        <div class="mt-2">対戦記録がありません</div>
        <a href="{{ route('battles.create') }}" class="btn btn-success mt-3">最初の対戦を記録</a>
    </div>
@else
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>結果</th><th>タイトル / 相手</th><th>フォーマット</th><th>ターン数</th><th>対戦日時</th><th></th></tr>
                </thead>
                <tbody>
                    @foreach($battles as $battle)
                    <tr id="battle-row-{{ $battle->id }}">
                        <td>
                            @if($battle->result==='win') <span class="badge bg-success">勝</span>
                            @elseif($battle->result==='lose') <span class="badge bg-danger">負</span>
                            @elseif($battle->result==='draw') <span class="badge bg-secondary">分</span>
                            @else <span class="badge bg-light text-dark">-</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('battles.show', $battle->id) }}" class="text-decoration-none fw-semibold">
                                {{ $battle->title ?? 'vs '.($battle->opponent_name ?? '名無し') }}
                            </a>
                            @if($battle->memo)
                                <div class="text-muted" style="font-size:.8rem">{{ Str::limit($battle->memo, 60) }}</div>
                            @endif
                        </td>
                        <td>{{ $battle->format ?? '-' }}</td>
                        <td>{{ $battle->turns_count }}</td>
                        <td>{{ $battle->played_at?->format('Y/m/d H:i') ?? '-' }}</td>
                        <td class="d-flex gap-1">
                            <a href="{{ route('battles.show', $battle->id) }}" class="btn btn-sm btn-outline-primary">開く</a>
                            <button class="btn btn-sm btn-outline-danger"
                                    onclick="deleteBattle({{ $battle->id }}, this)"
                                    title="削除">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $battles->links() }}</div>
@endif
@endsection
@push('scripts')
<script>
async function deleteBattle(id, btn) {
    if (!confirm('この対戦記録を削除しますか？')) return;
    btn.disabled = true;
    try {
        const res = await fetch(`/api/v1/battles/${id}`, {
            method: 'DELETE',
            headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content},
        });
        if (res.ok) {
            document.getElementById(`battle-row-${id}`).remove();
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
