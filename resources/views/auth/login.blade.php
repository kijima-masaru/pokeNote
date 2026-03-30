<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ログイン - pokeNote</title>
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
                    <h5 class="card-title mb-4">ログイン</h5>

                    @if(session('status'))
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle"></i> {{ session('status') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show">
                            {{ $errors->first() }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">メールアドレス</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}" required autofocus>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">パスワード</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" name="remember" class="form-check-input" id="remember">
                            <label class="form-check-label" for="remember">ログイン状態を保持する</label>
                        </div>
                        <button type="submit" class="btn btn-danger w-100 mb-2">
                            <i class="bi bi-box-arrow-in-right"></i> ログイン
                        </button>
                        <div class="text-center mb-2">
                            <a href="{{ route('password.request') }}" class="text-muted" style="font-size:.85rem">
                                パスワードをお忘れですか？
                            </a>
                        </div>
                    </form>
                    <div class="text-center text-muted" style="font-size:.9rem">
                        アカウントをお持ちでない方は
                        <a href="{{ route('register') }}">新規登録</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
