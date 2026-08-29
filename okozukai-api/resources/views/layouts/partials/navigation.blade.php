<nav class="global-navigation" id="global-navigation" aria-label="メインナビゲーション">
    <div class="navigation-inner">
        @if ($navigationType === 'parent')
        <a class="nav-links {{ request()->routeIs('parent.children.*') ? 'is-active' : '' }}" href="{{ route('parent.children.index') }}">
            <i class="fa-solid fa-children"></i>
            お子様一覧
        </a>
        <a class="nav-links {{ request()->routeIs('parent.family-account.*') ? 'is-active' : '' }}" href="{{ route('parent.family-account.index') }}">
            <i class="fa-solid fa-people-roof"></i>
            家族アカウント
        </a>
        <a class="nav-links {{ request()->routeIs('parent.chores-setting.*') ? 'is-active' : '' }}" href="{{ route('parent.chores-setting.index') }}">
            <i class="fa-solid fa-broom"></i>
            お手伝い設定
        </a>
        <a class="nav-links {{ request()->routeIs('parent.profile.*') ? 'is-active' : '' }}" href="{{ route('parent.profile.edit') }}">
            <i class="fa-solid fa-circle-user"></i>
            プロフィール
        </a>
        @auth
        @if (auth()->user()->role === 'parent')
        <form class="nav-logout-form" method="POST" action="{{ route('logout') }}" data-loading data-loading-message="ログアウトしています...">
            @csrf
            <button class="nav-links nav-link--button" type="submit">
                <i class="fa-solid fa-right-from-bracket"></i>
                ログアウト
            </button>
        </form>
        @endif
        @endauth
        @elseif ($navigationType === 'child')
        <a class="nav-links {{ request()->routeIs('child.home') ? 'is-active' : '' }}" href="{{ route('child.home') }}">
            <i class="fa-solid fa-house"></i>
                ホーム
        </a>
        <a class="nav-links {{ request()->routeIs('child.payment-record.*') ? 'is-active' : '' }}" href="{{ route('child.payment-record.create') }}">
            <i class="fa-solid fa-cash-register"></i>
                使ったお金を記録
        </a>
        <a class="nav-links {{ request()->routeIs('child.payment-history.*') ? 'is-active' : '' }}" href="{{ route('child.payment-history.index') }}">
            <i class="fa-solid fa-cart-shopping"></i>
                最近使ったお金
        </a>
        <a class="nav-links {{ request()->routeIs('child.chores.*') ? 'is-active' : '' }}" href="{{ route('child.chores.history') }}">
            <i class="fa-solid fa-broom"></i>
                お手伝いの記録
        </a>
        <a class="nav-links {{ request()->routeIs('child.savings.*') ? 'is-active' : '' }}" href="{{ route('child.savings.show') }}">
            <i class="fa-solid fa-sack-dollar"></i>
                ためたいお金
        </a>
        <a class="nav-links {{ request()->routeIs('child.profile.*') ? 'is-active' : '' }}" href="{{ route('child.profile.edit') }}">
            <i class="fa-solid fa-circle-user"></i>
                プロフィール
        </a>
        @auth
        @if (auth()->user()->role === 'child')
        <form class="nav-logout-form" method="POST" action="{{ route('child.logout') }}" data-loading data-loading-message="ログアウトしています...">
            @csrf
            <button class="nav-links nav-link--button" type="submit">
                <i class="fa-solid fa-right-from-bracket"></i>
                    ログアウト
            </button>
        </form>
        @endif
        @endauth
        @endif
    </div>
</nav>