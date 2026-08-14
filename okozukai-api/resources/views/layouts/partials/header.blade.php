<header class="app-header">
    <div class="header-inner">
        <a class="brand" href="{{ $navigationType === 'child' ? route('child.home') : ($navigationType === 'parent' ? route('parent.children.index') : route('login')) }}">
            <span class="brand-mark" aria-hidden="true">¥</span>
            <span>おこづかい</span>
        </a>

        @if ($showNavigation)
            <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="global-navigation">
                <span class="visually-hidden">メニューを開く</span>
                <span></span><span></span><span></span>
            </button>
        @endif
    </div>
</header>
