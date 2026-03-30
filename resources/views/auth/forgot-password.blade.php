<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>パスワードリセット - pokeNote</title>
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
                    <h5 class="card-title mb-2">パスワードをお忘れですか？</h5>
                    <p class="text-muted mb-4" style="font-size:.9rem">
                        登録済みのメールアドレスを入力してください。パスワードリセット用のリンクを送信します。
                    </p>

                    @if(session('status'))
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle"></i> {{ session('status') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">メールアドレス</label>
                            <input type="email" name="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}" required autofocus>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-danger w-100 mb-3">
                            <i class="bi bi-envelope"></i> リセットリンクを送信
                        </button>
                    </form>
                    <div class="text-center text-muted" style="font-size:.9rem">
                        <a href="{{ route('login') }}">
                            <i class="bi bi-arrow-left"></i> ログインに戻る
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
