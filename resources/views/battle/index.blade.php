@extends('layouts.app')
@section('title', '対戦履歴')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-trophy"></i> 対戦履歴</h4>
    <div class="d-flex gap-2">
        <button class="btn btn-sm btn-outline-secondary" onclick="exportBattlesCsv()">
            <i class="bi bi-download"></i> CSV出力
        </button>
        <a href="{{ route('battles.create') }}" class="btn btn-success btn-sm">
            <i class="bi bi-plus-circle"></i> 新規対戦
        </a>
    </div>
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
            <div class="col-sm-2">
                <label class="form-label mb-1" style="font-size:.8rem">タグ</label>
                <input type="text" name="tag" class="form-control form-control-sm"
                       placeholder="タグで検索" value="{{ request('tag') }}">
            </div>
            <div class="col-sm-1 d-flex gap-1 align-items-end">
                <button type="submit" class="btn btn-primary btn-sm flex-fill">
                    <i class="bi bi-search"></i>
                </button>
                @if(request()->hasAny(['opponent','result','format','date_from','date_to','tag']))
                <a href="{{ route('battles.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-x"></i>
                </a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- カレンダービュー -->
<div class="card border-0 shadow-sm mb-3" x-data="{calOpen: false}">
    <div class="card-header bg-white d-flex justify-content-between align-items-center"
         style="cursor:pointer" @click="calOpen=!calOpen">
        <strong><i class="bi bi-calendar3"></i> カレンダービュー
            <span class="badge bg-secondary ms-1" style="font-size:.7rem">{{ $calYear }}年{{ $calMonth }}月</span>
        </strong>
        <i class="bi" :class="calOpen?'bi-chevron-up':'bi-chevron-down'"></i>
    </div>
    <div x-show="calOpen" x-collapse>
        <div class="card-body">
            <!-- 月移動 -->
            <div class="d-flex align-items-center gap-2 mb-3">
                @php
                    $prevYear  = $calMonth === 1 ? $calYear - 1 : $calYear;
                    $prevMonth = $calMonth === 1 ? 12 : $calMonth - 1;
                    $nextYear  = $calMonth === 12 ? $calYear + 1 : $calYear;
                    $nextMonth = $calMonth === 12 ? 1 : $calMonth + 1;
                @endphp
                <a href="{{ route('battles.index', array_merge(request()->except(['cal_year','cal_month']), ['cal_year'=>$prevYear,'cal_month'=>$prevMonth])) }}"
                   class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-chevron-left"></i>
                </a>
                <strong class="mx-2">{{ $calYear }}年{{ $calMonth }}月</strong>
                <a href="{{ route('battles.index', array_merge(request()->except(['cal_year','cal_month']), ['cal_year'=>$nextYear,'cal_month'=>$nextMonth])) }}"
                   class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </div>
            @php
                $firstDay = \Carbon\Carbon::create($calYear, $calMonth, 1);
                $daysInMonth = $firstDay->daysInMonth;
                $startDow = $firstDay->dayOfWeek; // 0=Sun
                $weeks = ['日','月','火','水','木','金','土'];
            @endphp
            <div class="table-responsive">
                <table class="table table-bordered table-sm text-center mb-0" style="table-layout:fixed">
                    <thead>
                        <tr>
                            @foreach($weeks as $wi => $wd)
                                <th style="font-size:.8rem;{{ $wi==0?'color:#dc3545':($wi==6?'color:#0d6efd':'') }}">{{ $wd }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                    @php $day = 1; $col = $startDow; @endphp
                    @while($day <= $daysInMonth)
                    <tr>
                        @for($col2 = 0; $col2 < 7; $col2++)
                            @if(($col2 < $col && $day === 1) || $day > $daysInMonth)
                                <td class="bg-light"></td>
                            @else
                                @php
                                    $dateStr = sprintf('%04d-%02d-%02d', $calYear, $calMonth, $day);
                                    $entry = $calData[$dateStr] ?? null;
                                @endphp
                                <td style="height:56px;vertical-align:top;padding:2px 3px">
                                    <div style="font-size:.75rem;font-weight:{{ $entry ? '700' : '400' }};
                                         color:{{ $col2==0?'#dc3545':($col2==6?'#0d6efd':'inherit') }}">{{ $day }}</div>
                                    @if($entry)
                                        <a href="{{ route('battles.index', array_merge(request()->except(['date_from','date_to']), ['date_from'=>$dateStr,'date_to'=>$dateStr])) }}"
                                           class="text-decoration-none">
                                            <div style="font-size:.65rem;line-height:1.3">
                                                @if($entry->wins > 0)<span class="text-success">●×{{ $entry->wins }}</span>@endif
                                                @if($entry->loses > 0)<span class="text-danger ms-1">●×{{ $entry->loses }}</span>@endif
                                                @if($entry->draws > 0)<span class="text-muted ms-1">△×{{ $entry->draws }}</span>@endif
                                            </div>
                                        </a>
                                    @endif
                                </td>
                                @php $day++; @endphp
                            @endif
                            @php if($day > $daysInMonth) break; @endphp
                        @endfor
                        @while($col2 < 6) <td class="bg-light"></td> @php $col2++; @endphp @endwhile
                    </tr>
                    @php $col = 0; @endwhile
                    </tbody>
                </table>
            </div>
        </div>
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
                            @if($battle->tags)
                                <div class="mt-1">
                                    @foreach(array_filter(array_map('trim', explode(',', $battle->tags))) as $tag)
                                        <a href="{{ route('battles.index', ['tag' => $tag]) }}" class="badge rounded-pill bg-secondary text-decoration-none me-1" style="font-size:.7rem">{{ $tag }}</a>
                                    @endforeach
                                </div>
                            @endif
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
async function exportBattlesCsv() {
    // 全件取得（ページネーションなし）
    let page = 1, allBattles = [];
    while (true) {
        const res  = await fetch(`/api/v1/battles?per_page=100&page=${page}`, {
            headers: {'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content},
        });
        const data = await res.json();
        allBattles = allBattles.concat(data.data || []);
        if (!data.next_page_url) break;
        page++;
    }

    const header = ['id','結果','タイトル','相手名','フォーマット','ターン数','タグ','メモ','対戦日時'];
    const rows   = allBattles.map(b => [
        b.id,
        b.result || '',
        b.title  || '',
        b.opponent_name || '',
        b.format || '',
        b.turns_count ?? 0,
        b.tags   || '',
        (b.memo  || '').replace(/\n/g,' '),
        b.played_at ? b.played_at.replace('T',' ').slice(0,16) : '',
    ]);

    const csv  = [header, ...rows].map(r => r.map(c => `"${String(c).replace(/"/g,'""')}"`).join(',')).join('\n');
    const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
    const url  = URL.createObjectURL(blob);
    const a    = document.createElement('a');
    a.href     = url;
    a.download = `battles_${new Date().toISOString().slice(0,10)}.csv`;
    a.click();
    URL.revokeObjectURL(url);
}

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
