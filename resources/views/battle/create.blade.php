@extends('layouts.app')
@section('title', '新規対戦')
@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('battles.index') }}">対戦履歴</a></li>
        <li class="breadcrumb-item active">新規対戦</li>
    </ol>
</nav>
<h4 class="mb-3"><i class="bi bi-plus-circle"></i> 新規対戦記録</h4>
<div class="card border-0 shadow-sm" style="max-width:600px">
    <div class="card-body" x-data="battleCreate()">
        <div class="mb-3">
            <label class="form-label">タイトル（任意）</label>
            <input type="text" class="form-control" x-model="form.title" placeholder="例: ランクマッチ">
        </div>
        <div class="mb-3">
            <label class="form-label">対戦相手</label>
            <input type="text" class="form-control" x-model="form.opponent_name" placeholder="相手の名前・ID">
        </div>
        <div class="mb-3">
            <label class="form-label">フォーマット</label>
            <select class="form-select" x-model="form.format">
                <option value="">未選択</option>
                <option value="シングル">シングル</option>
                <option value="ダブル">ダブル</option>
                <option value="トリプル">トリプル</option>
                <option value="その他">その他</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">対戦日時</label>
            <input type="datetime-local" class="form-control" x-model="form.played_at">
        </div>
        <div class="mb-3">
            <label class="form-label">結果</label>
            <div class="d-flex gap-2">
                <button type="button" class="btn" :class="form.result==='win'?'btn-success':'btn-outline-success'" @click="form.result='win'">勝ち</button>
                <button type="button" class="btn" :class="form.result==='lose'?'btn-danger':'btn-outline-danger'" @click="form.result='lose'">負け</button>
                <button type="button" class="btn" :class="form.result==='draw'?'btn-secondary':'btn-outline-secondary'" @click="form.result='draw'">引き分け</button>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">メモ</label>
            <textarea class="form-control" x-model="form.memo" rows="3" placeholder="相手の構成など"></textarea>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-success" @click="submit()">
                <i class="bi bi-check-circle"></i> 対戦を作成してターン記録へ
            </button>
            <a href="{{ route('battles.index') }}" class="btn btn-outline-secondary">キャンセル</a>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
function battleCreate() {
    return {
        form: {title:'',opponent_name:'',format:'',played_at:'',result:'',memo:''},
        async submit() {
            const res = await fetch('/api/v1/battles', {
                method: 'POST',
                headers: {'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},
                body: JSON.stringify(this.form),
            });
            if (res.ok) {
                const battle = await res.json();
                window.location.href = `/battles/${battle.id}`;
            } else { alert('エラーが発生しました'); }
        },
    };
}
</script>
@endpush
