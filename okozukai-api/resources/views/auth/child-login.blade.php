@extends('layouts.app')

@section('title', '子どもログイン | おこづかい')

@section('content')
    <section class="auth-card">
        <div class="page-heading auth-heading">
            <p class="page-heading__eyebrow">CHILD LOGIN</p>
            <h1>お子様ログイン</h1>
            <p class="auth-heading__description">家族コードとログインIDを入力してください。</p>
        </div>

        {{-- ログイン処理 --}}
        <form id="child-login-form" method="POST" action="{{ route('child.login.store') }}" data-child-login-form data-loading data-loading-message="ログインしています..." novalidate>
            @csrf

            <div class="form-group">
                <label class="form-label" for="family_code">家族コード
                    <span class="required-label">必須</span>
                </label>
                <input
                    class="form-control @error('family_code') is-invalid @enderror"
                    id="family_code"
                    name="family_code"
                    type="text"
                    value="{{ old('family_code') }}"
                    minlength="8"
                    maxlength="15"
                    inputmode="numeric"
                    autocomplete="off"
                    pattern="[0-9]{8}"
                    required
                    autofocus
                >
                @error('family_code')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="login_id">ログインID <span class="required-label">必須</span></label>
                <input
                    class="form-control @error('login_id') is-invalid @enderror"
                    id="login_id"
                    name="login_id"
                    type="text"
                    value="{{ old('login_id') }}"
                    maxlength="100"
                    autocomplete="username"
                    required
                >
                @error('login_id')
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
                <input name="save_login" type="checkbox" value="1" data-save-child-login @checked(old('save_login'))>
                <span>ログイン情報を保存する</span>
            </label>

            <p class="form-help login-storage-note">家族コードとログインIDのみ保存します。パスワードは保存しません。</p>

            <button class="button button--primary button--block" type="submit">ログイン</button>
        </form>

        <p class="auth-link">パスワードを忘れた場合は保護者に変更を依頼してください。</p>

        <p class="auth-link">保護者の方は <a href="{{ route('login') }}">保護者ログイン</a></p>
    </section>
@endsection
