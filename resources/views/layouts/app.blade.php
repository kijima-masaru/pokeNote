<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'pokeNote') - ポケモン対戦ノート</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root { --poke-red: #cc0000; --poke-dark: #1a1a2e; }
        body { background-color: #f8f9fa; }
        .navbar-brand { font-weight: bold; color: var(--poke-red) !important; font-size: 1.4rem; }
        .sidebar { min-height: calc(100vh - 56px); background: var(--poke-dark); }
        .sidebar .nav-link { color: #adb5bd; padding: .5rem 1rem; border-radius: 6px; margin-bottom: 2px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background: rgba(255,255,255,.1); color: #fff; }
        .sidebar .nav-link i { width: 1.4rem; }
        .type-badge { display: inline-block; padding: 2px 8px; border-radius: 12px; font-size: .75rem; font-weight: 600; color: #fff; }
        .type-normal{background:#A8A878}.type-fire{background:#F08030}.type-water{background:#6890F0}
        .type-electric{background:#F8D030;color:#333}.type-grass{background:#78C850}.type-ice{background:#98D8D8;color:#333}
        .type-fighting{background:#C03028}.type-poison{background:#A040A0}.type-ground{background:#E0C068;color:#333}
        .type-flying{background:#A890F0}.type-psychic{background:#F85888}.type-bug{background:#A8B820}
        .type-rock{background:#B8A038}.type-ghost{background:#705898}.type-dragon{background:#7038F8}
        .type-dark{background:#705848}.type-steel{background:#B8B8D0;color:#333}.type-fairy{background:#EE99AC;color:#333}
        .stat-bar { height: 8px; border-radius: 4px; background: #e9ecef; overflow: hidden; }
        .stat-bar-fill { height: 100%; border-radius: 4px; transition: width .3s; }
        .pokemon-card { transition: transform .15s; cursor: pointer; }
        .pokemon-card:hover { transform: translateY(-3px); box-shadow: 0 4px 15px rgba(0,0,0,.1); }
    </style>
    @stack('styles')
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ route('dashboard') }}">
            <i class="bi bi-journal-code"></i> pokeNote
        </a>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('damage-calc.index') }}" class="btn btn-sm btn-outline-warning">
                <i class="bi bi-calculator"></i> ダメージ計算
            </a>
            <a href="{{ route('battles.create') }}" class="btn btn-sm btn-outline-success">
                <i class="bi bi-plus-circle"></i> 対戦記録
            </a>
            @auth
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle"></i> {{ Auth::user()->name }}
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <span class="dropdown-item-text text-muted" style="font-size:.85rem">
                            {{ Auth::user()->email }}
                        </span>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('profile.show') }}">
                            <i class="bi bi-person-gear"></i> プロフィール設定
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="bi bi-box-arrow-right"></i> ログアウト
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
            @endauth
        </div>
    </div>
</nav>
<div class="container-fluid">
    <div class="row">
        <nav class="col-md-2 d-md-block sidebar py-3 px-2">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                        <i class="bi bi-house"></i> ダッシュボード
                    </a>
                </li>
                <li class="nav-item mt-2"><small class="text-secondary px-2 text-uppercase" style="font-size:.7rem">図鑑</small></li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('pokemon.*') ? 'active' : '' }}" href="{{ route('pokemon.index') }}">
                        <i class="bi bi-collection"></i> ポケモン図鑑
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('moves.*') ? 'active' : '' }}" href="{{ route('moves.index') }}">
                        <i class="bi bi-lightning-charge"></i> わざ一覧
                    </a>
                </li>
                <li class="nav-item mt-2"><small class="text-secondary px-2 text-uppercase" style="font-size:.7rem">マスター管理</small></li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('master.pokemon') ? 'active' : '' }}" href="{{ route('master.pokemon') }}">
                        <i class="bi bi-database-add"></i> ポケモン登録
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('master.moves') ? 'active' : '' }}" href="{{ route('master.moves') }}">
                        <i class="bi bi-lightning-charge"></i> わざ管理
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('master.abilities') ? 'active' : '' }}" href="{{ route('master.abilities') }}">
                        <i class="bi bi-stars"></i> 特性管理
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('master.items') ? 'active' : '' }}" href="{{ route('master.items') }}">
                        <i class="bi bi-bag"></i> 持ち物管理
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('master.import') ? 'active' : '' }}" href="{{ route('master.import') }}">
                        <i class="bi bi-cloud-download"></i> PokeAPIインポート
                    </a>
                </li>
                <li class="nav-item mt-2"><small class="text-secondary px-2 text-uppercase" style="font-size:.7rem">マイポケモン</small></li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('custom-pokemon.*') ? 'active' : '' }}" href="{{ route('custom-pokemon.index') }}">
                        <i class="bi bi-star"></i> マイポケモン
                    </a>
                </li>
                <li class="nav-item mt-2"><small class="text-secondary px-2 text-uppercase" style="font-size:.7rem">ツール</small></li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('damage-calc.*') ? 'active' : '' }}" href="{{ route('damage-calc.index') }}">
                        <i class="bi bi-calculator"></i> ダメージ計算
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('compare') ? 'active' : '' }}" href="{{ route('compare') }}">
                        <i class="bi bi-bar-chart-steps"></i> ポケモン比較
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('battles.*') ? 'active' : '' }}" href="{{ route('battles.index') }}">
                        <i class="bi bi-trophy"></i> 対戦履歴
                    </a>
                </li>
            </ul>
        </nav>
        <main class="col-md-10 ms-sm-auto px-4 py-3">
            @yield('content')
        </main>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.0/dist/cdn.min.js" defer></script>
@stack('scripts')

<!-- トースト通知コンテナ -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index:1100" id="toastContainer">
@foreach([
    ['key'=>'success',          'bg'=>'bg-success',  'icon'=>'bi-check-circle-fill'],
    ['key'=>'status',           'bg'=>'bg-success',  'icon'=>'bi-check-circle-fill'],
    ['key'=>'success_info',     'bg'=>'bg-success',  'icon'=>'bi-check-circle-fill'],
    ['key'=>'success_password', 'bg'=>'bg-success',  'icon'=>'bi-check-circle-fill'],
    ['key'=>'error',            'bg'=>'bg-danger',   'icon'=>'bi-exclamation-circle-fill'],
    ['key'=>'warning',          'bg'=>'bg-warning',  'icon'=>'bi-exclamation-triangle-fill'],
    ['key'=>'info',             'bg'=>'bg-info',     'icon'=>'bi-info-circle-fill'],
] as $t)
@if(session($t['key']))
<div class="toast align-items-center text-white {{ $t['bg'] }} border-0 show" role="alert" aria-live="assertive">
    <div class="d-flex">
        <div class="toast-body">
            <i class="bi {{ $t['icon'] }} me-1"></i> {{ session($t['key']) }}
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
</div>
@endif
@endforeach
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('#toastContainer .toast').forEach(function (el) {
        var toast = new bootstrap.Toast(el, { delay: 4000 });
        toast.show();
    });
});
// グローバルトースト表示関数（JS側から呼び出し可能）
window.showToast = function(message, type) {
    type = type || 'success';
    var bgMap = { success: 'bg-success', error: 'bg-danger', warning: 'bg-warning', info: 'bg-info' };
    var iconMap = { success: 'bi-check-circle-fill', error: 'bi-exclamation-circle-fill', warning: 'bi-exclamation-triangle-fill', info: 'bi-info-circle-fill' };
    var el = document.createElement('div');
    el.className = 'toast align-items-center text-white ' + (bgMap[type] || 'bg-success') + ' border-0';
    el.setAttribute('role', 'alert');
    el.innerHTML = '<div class="d-flex"><div class="toast-body"><i class="bi ' + (iconMap[type] || 'bi-check-circle-fill') + ' me-1"></i> ' + message + '</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>';
    document.getElementById('toastContainer').appendChild(el);
    var toast = new bootstrap.Toast(el, { delay: 4000 });
    toast.show();
    el.addEventListener('hidden.bs.toast', function () { el.remove(); });
};
</script>
</body>
</html>
