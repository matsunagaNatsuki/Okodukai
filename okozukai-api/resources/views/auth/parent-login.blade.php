@extends('layouts.app')

@section('title', '保護者ログイン | おこづかい')

@section('content')
    <section class="auth-card">
        <div class="page-heading auth-heading">
            <p class="page-heading__eyebrow">PARENT LOGIN</p>
            <h1>保護者ログイン</h1>
            <p class="auth-heading__description">登録したメールアドレスとパスワードを入力してください。</p>
        </div>

        <form method="POST" action="{{ route('login.store') }}" data-loading data-loading-message="ログインしています...">
            @csrf

            <div class="form-group">
                <label class="form-label" for="email">メールアドレス <span class="required-label">必須</span></label>
                <input
                    class="form-control @error('email') is-invalid @enderror"
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email', $savedEmail) }}"
                    maxlength="255"
                    inputmode="email"
                    autocomplete="email"
                    required
                    autofocus
                >
                @error('email')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="password">パスワード <span class="required-label">必須</span></label>
                <div class="password-field">
                    <input
                        class="form-control @error('password') is-invalid @enderror"
                        id="password"
                        name="password"
                        type="password"
                        autocomplete="current-password"
                        required
                    >
                    <button class="password-toggle" type="button" data-password-toggle="#password" aria-label="パスワードを表示する">表示</button>
                </div>
                @error('password')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <label class="checkbox-field">
                <input
                    name="save_email"
                    type="checkbox"
                    value="1"
                    @checked(old('save_email', filled($savedEmail)))
                >
                <span>メールアドレスを保存する</span>
            </label>

            <button class="button button--primary button--block" type="submit">ログイン</button>
        </form>

        <p class="auth-link"><a href="{{ route('password.request') }}">パスワードを忘れた方はこちら</a></p>
        <p class="auth-link">初めて利用する方は <a href="{{ route('register') }}">保護者新規登録</a></p>
    </section>
@endsection
