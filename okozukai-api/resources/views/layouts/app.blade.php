<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'おこづかい')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
@php
    $navigationType = $navigationType ?? (request()->routeIs('parent.*') ? 'parent' : (request()->routeIs('child.*') ? 'child' : 'guest'));
    $showNavigation = $showNavigation ?? ! request()->routeIs('login', 'register', 'password.*', 'child.login');
@endphp
<body class="app-body app-body--{{ $navigationType }}">
    <div class="app-shell">
        @include('layouts.partials.header', ['navigationType' => $navigationType])

        @if ($showNavigation)
            @include('layouts.partials.navigation', ['navigationType' => $navigationType])
        @endif

        <main class="app-main" id="main-content">
            <div class="content-container">
                @include('layouts.partials.flash-message')
                @include('layouts.partials.validation-errors')

                @yield('content')
            </div>
        </main>

        @include('layouts.partials.footer')
        @include('layouts.partials.modal')
        @include('layouts.partials.loading')
    </div>

    @stack('scripts')
</body>
</html>
    </main>
</body>
</html>
