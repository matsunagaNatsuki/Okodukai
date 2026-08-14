@extends('layouts.app')

@section('title', 'お子様一覧 | おこづかい')

@section('content')
    <div class="page-heading page-heading--with-action">
        <div>
            <p class="page-heading__eyebrow">CHILDREN</p>
            <h1>お子様一覧</h1>
            <p class="page-heading__description">管理するお子様を選んでください。</p>
        </div>
        <a class="button button--primary" href="{{ route('parent.family-account.index') }}">家族アカウント</a>
    </div>

    @if ($children->isEmpty())
        <section class="empty-state">
            <span class="empty-state__icon" aria-hidden="true">👪</span>
            <h2>お子様はまだ登録されていません</h2>
            <p>家族アカウントからお子様を追加してください</p>
            <a class="button button--primary" href="{{ route('parent.family-account.index') }}">家族アカウントへ</a>
        </section>
    @else
        <div class="child-grid">
            @foreach ($children as $child)
                <a class="child-card" href="{{ route('parent.children.show', $child) }}">
                    @if ($child->profile_image)
                        <img
                            class="child-card__avatar"
                            src="{{ str_starts_with($child->profile_image, 'http://') || str_starts_with($child->profile_image, 'https://') || str_starts_with($child->profile_image, '/') ? $child->profile_image : \Illuminate\Support\Facades\Storage::disk('public')->url($child->profile_image) }}"
                            alt="{{ $child->name }}のプロフィール画像"
                        >
                    @else
                        <span class="child-card__avatar child-card__avatar--default" aria-label="プロフィール画像未設定">
                            {{ mb_substr($child->name, 0, 1) }}
                        </span>
                    @endif

                    <span class="child-card__details">
                        <strong class="child-card__name">{{ $child->name }}</strong>
                        <span class="child-card__login-id">ログインID：{{ $child->login_id }}</span>
                    </span>
                    <span class="child-card__arrow" aria-hidden="true">›</span>
                </a>
            @endforeach
        </div>
    @endif
@endsection
