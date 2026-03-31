<!DOCTYPE html>
<html lang="ja" id="htmlRoot">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'pokeNote') - ポケモン対戦ノート</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script>
        // ダークモード: localStorageまたはシステム設定を読んでちらつきなく適用
        (function() {
            var pref = localStorage.getItem('pokeNote_darkMode');
            var dark = pref === 'dark' || (pref === null && window.matchMedia('(prefers-color-scheme: dark)').matches);
            if (dark) document.documentElement.setAttribute('data-bs-theme', 'dark');
        })();
    </script>
    <style>
        :root { --poke-red: #cc0000; --poke-dark: #1a1a2e; }
        body { background-color: #f8f9fa; }
        [data-bs-theme="dark"] body { background-color: #121212; }
        [data-bs-theme="dark"] .sidebar { background: #0d0d1a; }
        [data-bs-theme="dark"] .offcanvas-sidebar { background: #0d0d1a !important; }
        [data-bs-theme="dark"] .pokemon-card:hover { box-shadow: 0 4px 15px rgba(255,255,255,.06); }
        .navbar-brand { font-weight: bold; color: var(--poke-red) !important; font-size: 1.4rem; }

        /* ===== サイドバー (デスクトップ) ===== */
        .sidebar { min-height: calc(100vh - 56px); background: var(--poke-dark); }
        .sidebar .nav-link { color: #adb5bd; padding: .5rem 1rem; border-radius: 6px; margin-bottom: 2px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background: rgba(255,255,255,.1); color: #fff; }
        .sidebar .nav-link i { width: 1.4rem; }

        /* ===== オフキャンバスサイドバー (モバイル) ===== */
        .offcanvas-sidebar { background: var(--poke-dark) !important; }
        .offcanvas-sidebar .nav-link { color: #adb5bd; padding: .5rem 1rem; border-radius: 6px; margin-bottom: 2px; }
        .offcanvas-sidebar .nav-link:hover, .offcanvas-sidebar .nav-link.active { background: rgba(255,255,255,.1); color: #fff; }
        .offcanvas-sidebar .nav-link i { width: 1.4rem; }
        .offcanvas-sidebar .offcanvas-header { border-bottom: 1px solid rgba(255,255,255,.1); }
        .offcanvas-sidebar .btn-close { filter: invert(1); }

        /* タイプバッジ */
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

        /* モバイル調整 */
        @media (max-width: 767.98px) {
            .sidebar { display: none !important; }
            main.col-md-10 { padding-left: 1rem !important; padding-right: 1rem !important; }
        }
        /* タッチデバイス向けボタン最小サイズ */
        @media (max-width: 767.98px) {
            .btn { min-height: 36px; }
            .form-control, .form-select { font-size: 16px !important; } /* iOS zoom防止 */
        }
    </style>
    @stack('styles')
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <!-- モバイル: ハンバーガーボタン -->
        <button class="btn btn-sm btn-outline-secondary me-2 d-md-none"
                type="button"
                data-bs-toggle="offcanvas"
                data-bs-target="#sidebarOffcanvas"
                aria-controls="sidebarOffcanvas">
            <i class="bi bi-list" style="font-size:1.1rem"></i>
        </button>

        <a class="navbar-brand" href="{{ route('dashboard') }}">
            <i class="bi bi-journal-code"></i> pokeNote
        </a>

        <div class="d-flex align-items-center gap-2 ms-auto">
            <button id="darkModeToggle" class="btn btn-sm btn-outline-secondary" title="ダークモード切り替え" onclick="toggleDarkMode()">
                <i class="bi bi-moon-stars-fill" id="darkModeIcon"></i>
            </button>
            <a href="{{ route('damage-calc.index') }}" class="btn btn-sm btn-outline-warning d-none d-sm-inline-flex">
                <i class="bi bi-calculator"></i> ダメージ計算
            </a>
            <a href="{{ route('battles.create') }}" class="btn btn-sm btn-outline-success">
                <i class="bi bi-plus-circle"></i> <span class="d-none d-sm-inline">対戦記録</span>
            </a>
            @auth
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle"></i> <span class="d-none d-sm-inline">{{ Auth::user()->name }}</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <span class="dropdown-item-text text-muted" style="font-size:.85rem">
                            {{ Auth::user()->name }}<br>
                            <small>{{ Auth::user()->email }}</small>
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

<!-- モバイル用オフキャンバスサイドバー -->
<div class="offcanvas offcanvas-start offcanvas-sidebar" tabindex="-1" id="sidebarOffcanvas" aria-labelledby="sidebarOffcanvasLabel">
    <div class="offcanvas-header">
        <h6 class="offcanvas-title text-white" id="sidebarOffcanvasLabel">
            <i class="bi bi-journal-code" style="color:var(--poke-red)"></i> pokeNote
        </h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body py-2 px-2">
        @include('layouts._sidebar')
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <!-- デスクトップサイドバー -->
        <nav class="col-md-2 d-none d-md-block sidebar py-3 px-2">
            @include('layouts._sidebar')
        </nav>
        <main class="col-12 col-md-10 ms-sm-auto px-4 py-3">
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
function toggleDarkMode() {
    var isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
    var next = isDark ? 'light' : 'dark';
    document.documentElement.setAttribute('data-bs-theme', next);
    localStorage.setItem('pokeNote_darkMode', next);
    updateDarkModeIcon();
}
function updateDarkModeIcon() {
    var isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
    var icon = document.getElementById('darkModeIcon');
    if (icon) {
        icon.className = isDark ? 'bi bi-sun-fill' : 'bi bi-moon-stars-fill';
    }
}
document.addEventListener('DOMContentLoaded', function () {
    updateDarkModeIcon();
    document.querySelectorAll('#toastContainer .toast').forEach(function (el) {
        var toast = new bootstrap.Toast(el, { delay: 4000 });
        toast.show();
    });
    // オフキャンバス内のリンクをクリックしたら閉じる
    document.querySelectorAll('#sidebarOffcanvas a').forEach(function (a) {
        a.addEventListener('click', function () {
            var oc = bootstrap.Offcanvas.getInstance(document.getElementById('sidebarOffcanvas'));
            if (oc) oc.hide();
        });
    });
});
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
