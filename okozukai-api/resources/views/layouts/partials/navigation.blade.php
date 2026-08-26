<nav class="global-navigation" id="global-navigation" aria-label="メインナビゲーション">
    <div class="navigation-inner">
        @if ($navigationType === 'parent')
            <a class="nav-link {{ request()->routeIs('parent.children.*') ? 'is-active' : '' }}" href="{{ route('parent.children.index') }}">お子様一覧</a>
            <a class="nav-link {{ request()->routeIs('parent.family-account.*') ? 'is-active' : '' }}" href="{{ route('parent.family-account.index') }}">家族アカウント</a>
            <a class="nav-link {{ request()->routeIs('parent.chores-setting.*') ? 'is-active' : '' }}" href="{{ route('parent.chores-setting.index') }}">お手伝い設定</a>
            <a class="nav-link {{ request()->routeIs('parent.profile.*') ? 'is-active' : '' }}" href="{{ route('parent.profile.edit') }}">プロフィール</a>
            @auth
                @if (auth()->user()->role === 'parent')
                    <form class="nav-logout-form" method="POST" action="{{ route('logout') }}" data-loading data-loading-message="ログアウトしています...">
                        @csrf
                        <button class="nav-link nav-link--button" type="submit">ログアウト</button>
                    </form>
                @endif
            @endauth
        @elseif ($navigationType === 'child')
            <a class="nav-link {{ request()->routeIs('child.home') ? 'is-active' : '' }}" href="{{ route('child.home') }}">ホーム</a>
            <a class="nav-link {{ request()->routeIs('child.payment-record.*') ? 'is-active' : '' }}" href="{{ route('child.payment-record.create') }}">つかったもの</a>
            <a class="nav-link {{ request()->routeIs('child.payment-history.*') ? 'is-active' : '' }}" href="{{ route('child.payment-history.index') }}">支出履歴</a>
            <a class="nav-link {{ request()->routeIs('child.chores.*') ? 'is-active' : '' }}" href="{{ route('child.chores.history') }}">お手伝い</a>
            <a class="nav-link {{ request()->routeIs('child.savings.*') ? 'is-active' : '' }}" href="{{ route('child.savings.show') }}">貯金目標</a>
            <a class="nav-link {{ request()->routeIs('child.profile.*') ? 'is-active' : '' }}" href="{{ route('child.profile.edit') }}">プロフィール</a>
            @auth
                @if (auth()->user()->role === 'child')
                    <form class="nav-logout-form" method="POST" action="{{ route('child.logout') }}" data-loading data-loading-message="ログアウトしています...">
                        @csrf
                        <button class="nav-link nav-link--button" type="submit">ログアウト</button>
                    </form>
                @endif
            @endauth
        @endif
    </div>
</nav>
