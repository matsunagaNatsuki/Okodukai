@extends('layouts.app')

@section('title', '保護者新規登録 | おこづかい')

@section('content')
    <section class="auth-card">
        <div class="page-heading auth-heading">
            <p class="page-heading__eyebrow">PARENT SIGN UP</p>
            <h1>保護者新規登録</h1>
            <p class="auth-heading__description">最初の保護者を登録して、家族のおこづかい管理を始めましょう。</p>
        </div>

        <form method="POST" action="{{ route('parent.register.store') }}" data-loading data-loading-message="登録しています..." novalidate>
            @csrf

            <div class="form-group">
                <label class="form-label" for="name">名前 <span class="required-label">必須</span></label>
                <input
                    class="form-control @error('name') is-invalid @enderror"
                    id="name"
                    name="name"
                    type="text"
                    value="{{ old('name') }}"
                    maxlength="100"
                    autocomplete="name"
                    required
                    autofocus
                >
                @error('name')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="email">メールアドレス <span class="required-label">必須</span></label>
                <input
                    class="form-control @error('email') is-invalid @enderror"
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    maxlength="255"
                    inputmode="email"
                    autocomplete="email"
                    required
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
                        minlength="8"
                        autocomplete="new-password"
                        required
                    >
                    <button class="password-toggle" type="button" data-password-toggle="#password" aria-label="パスワードを表示する">表示</button>
                </div>
                <p class="form-help">8文字以上で入力してください。</p>
                @error('password')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="password_confirmation">パスワード確認 <span class="required-label">必須</span></label>
                <div class="password-field">
                    <input
                        class="form-control"
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        minlength="8"
                        autocomplete="new-password"
                        required
                    >
                    <button class="password-toggle" type="button" data-password-toggle="#password_confirmation" aria-label="パスワード確認を表示する">表示</button>
                </div>
            </div>

            <button class="button button--primary button--block" type="submit">家族を作成して登録する</button>
        </form>

        <p class="auth-link">すでに登録済みの方は <a href="{{ route('login') }}">ログイン</a></p>
    </section>
@endsection
