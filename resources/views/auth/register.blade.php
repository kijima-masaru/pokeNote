<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>新規登録 - pokeNote</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root { --poke-red: #cc0000; }
        body { background-color: #f8f9fa; min-height: 100vh; display: flex; align-items: center; }
        .auth-card { max-width: 420px; width: 100%; }
        .brand { font-size: 2rem; font-weight: bold; color: var(--poke-red); }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 auth-card">
            <div class="text-center mb-4">
                <div class="brand"><i class="bi bi-journal-code"></i> pokeNote</div>
                <p class="text-muted">ポケモン対戦ノート</p>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="card-title mb-4">新規アカウント登録</h5>

                    <form method="POST" action="{{ route('register') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">名前</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" required autofocus>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">メールアドレス</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">パスワード <small class="text-muted">（8文字以上）</small></label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label class="form-label">パスワード（確認）</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-danger w-100 mb-3">
                            <i class="bi bi-person-plus"></i> 登録する
                        </button>
                    </form>
                    <div class="text-center text-muted" style="font-size:.9rem">
                        既にアカウントをお持ちの方は
                        <a href="{{ route('login') }}">ログイン</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
