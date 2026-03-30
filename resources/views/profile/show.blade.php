@extends('layouts.app')
@section('title', 'プロフィール設定')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-person-circle"></i> プロフィール設定</h4>
</div>

<div class="row g-3">
    <!-- 基本情報 -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><strong>基本情報</strong></div>
            <div class="card-body">
                @if(session('success_info'))
                    <div class="alert alert-success py-2">
                        <i class="bi bi-check-circle"></i> {{ session('success_info') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('profile.update-info') }}">
                    @csrf
                    @method('PATCH')
                    <div class="mb-3">
                        <label class="form-label">名前</label>
                        <input type="text" name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $user->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">メールアドレス</label>
                        <input type="email" name="email"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email', $user->email) }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-1 text-muted" style="font-size:.8rem">
                        <i class="bi bi-clock"></i> 登録日: {{ $user->created_at->format('Y年m月d日') }}
                    </div>
                    <button type="submit" class="btn btn-primary mt-2">
                        <i class="bi bi-check-circle"></i> 情報を更新する
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- パスワード変更 -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><strong>パスワード変更</strong></div>
            <div class="card-body">
                @if(session('success_password'))
                    <div class="alert alert-success py-2">
                        <i class="bi bi-check-circle"></i> {{ session('success_password') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('profile.update-password') }}">
                    @csrf
                    @method('PATCH')
                    <div class="mb-3">
                        <label class="form-label">現在のパスワード</label>
                        <input type="password" name="current_password"
                               class="form-control @error('current_password') is-invalid @enderror"
                               autocomplete="current-password" required>
                        @error('current_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">新しいパスワード <small class="text-muted">（8文字以上）</small></label>
                        <input type="password" name="password"
                               class="form-control @error('password') is-invalid @enderror"
                               autocomplete="new-password" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">新しいパスワード（確認）</label>
                        <input type="password" name="password_confirmation"
                               class="form-control" autocomplete="new-password" required>
                    </div>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-key"></i> パスワードを変更する
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- マイポケモン・対戦記録の統計 -->
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><strong>利用状況</strong></div>
            <div class="card-body">
                <div class="row text-center g-3">
                    <div class="col-6 col-md-3">
                        <div class="border rounded p-3">
                            <div class="display-6 fw-bold text-primary">{{ $user->customPokemon()->count() }}</div>
                            <div class="text-muted" style="font-size:.85rem">マイポケモン</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="border rounded p-3">
                            <div class="display-6 fw-bold text-success">{{ $user->battles()->where('result','win')->count() }}</div>
                            <div class="text-muted" style="font-size:.85rem">勝利数</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="border rounded p-3">
                            <div class="display-6 fw-bold text-danger">{{ $user->battles()->where('result','lose')->count() }}</div>
                            <div class="text-muted" style="font-size:.85rem">敗北数</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="border rounded p-3">
                            <div class="display-6 fw-bold text-secondary">{{ $user->battles()->count() }}</div>
                            <div class="text-muted" style="font-size:.85rem">対戦記録数</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- アカウント削除 -->
    <div class="col-12">
        <div class="card border-0 shadow-sm border-danger">
            <div class="card-header bg-white text-danger"><strong><i class="bi bi-exclamation-triangle"></i> 危険ゾーン</strong></div>
            <div class="card-body">
                <p class="text-muted" style="font-size:.9rem">
                    アカウントを削除すると、マイポケモン・対戦履歴などすべてのデータが完全に削除されます。この操作は取り消せません。
                </p>

                @if($errors->has('delete_password'))
                    <div class="alert alert-danger py-2">{{ $errors->first('delete_password') }}</div>
                @endif

                <button class="btn btn-outline-danger btn-sm"
                        data-bs-toggle="collapse" data-bs-target="#deleteAccountForm">
                    <i class="bi bi-trash"></i> アカウントを削除する
                </button>
                <div class="collapse mt-3" id="deleteAccountForm">
                    <form method="POST" action="{{ route('profile.destroy') }}"
                          onsubmit="return confirm('本当にアカウントを削除しますか？この操作は取り消せません。')">
                        @csrf
                        @method('DELETE')
                        <div class="mb-3" style="max-width:320px">
                            <label class="form-label">確認のためパスワードを入力してください</label>
                            <input type="password" name="password"
                                   class="form-control form-control-sm" required>
                        </div>
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="bi bi-trash"></i> 削除を確定する
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
