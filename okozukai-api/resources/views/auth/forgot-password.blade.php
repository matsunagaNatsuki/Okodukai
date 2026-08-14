@extends('layouts.app')

@section('title', 'パスワード再設定 | おこづかい')

@section('content')
    <section class="auth-card">
        <div class="page-heading auth-heading">
            <p class="page-heading__eyebrow">RESET PASSWORD</p>
            <h1>パスワード再設定</h1>
            <p class="auth-heading__description">保護者のメールアドレスへ再設定用リンクを送信します。</p>
        </div>

        <form method="POST" action="{{ route('password.email') }}" data-loading data-loading-message="送信しています...">
            @csrf

            <div class="form-group">
                <label class="form-label" for="email">メールアドレス <span class="required-label">必須</span></label>
                <input
                    class="form-control @error('email') is-invalid @enderror"
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    maxlength="255"
                    autocomplete="email"
                    required
                    autofocus
                >
                @error('email')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <button class="button button--primary button--block" type="submit">再設定用リンクを送信する</button>
        </form>

        <p class="auth-link"><a href="{{ route('login') }}">保護者ログインへ戻る</a></p>
    </section>
@endsection
