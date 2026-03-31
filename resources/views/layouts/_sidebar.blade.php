<ul class="nav flex-column">
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
            <i class="bi bi-house"></i> ダッシュボード
        </a>
    </li>
    <li class="nav-item mt-2"><small class="text-secondary px-2 text-uppercase" style="font-size:.7rem">図鑑</small></li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('pokemon.index') ? 'active' : '' }}" href="{{ route('pokemon.index') }}">
            <i class="bi bi-collection"></i> ポケモン図鑑
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('moves.*') ? 'active' : '' }}" href="{{ route('moves.index') }}">
            <i class="bi bi-lightning-charge"></i> わざ一覧
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('pokemon.type-chart') ? 'active' : '' }}" href="{{ route('pokemon.type-chart') }}">
            <i class="bi bi-grid-3x3"></i> タイプ相性表
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
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('teams.*') ? 'active' : '' }}" href="{{ route('teams.index') }}">
            <i class="bi bi-people"></i> チームビルダー
        </a>
    </li>
    <li class="nav-item mt-2"><small class="text-secondary px-2 text-uppercase" style="font-size:.7rem">ツール</small></li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('damage-calc.index') ? 'active' : '' }}" href="{{ route('damage-calc.index') }}">
            <i class="bi bi-calculator"></i> ダメージ計算
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('damage-calc.speed-compare') ? 'active' : '' }}" href="{{ route('damage-calc.speed-compare') }}">
            <i class="bi bi-speedometer2"></i> 素早さ比較
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
