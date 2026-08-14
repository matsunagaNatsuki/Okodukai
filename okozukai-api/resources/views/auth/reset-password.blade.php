@extends('layouts.app')

@section('title', '新しいパスワード | おこづかい')

@section('content')
    <section class="auth-card">
        <div class="page-heading auth-heading">
            <p class="page-heading__eyebrow">NEW PASSWORD</p>
            <h1>新しいパスワード</h1>
            <p class="auth-heading__description">新しく使用するパスワードを入力してください。</p>
        </div>

        <form method="POST" action="{{ route('password.update') }}" data-loading data-loading-message="更新しています...">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div class="form-group">
                <label class="form-label" for="email">メールアドレス</label>
                <input
                    class="form-control @error('email') is-invalid @enderror"
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email', $request->email) }}"
                    autocomplete="email"
                    required
                    readonly
                >
                @error('email')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="password">新しいパスワード <span class="required-label">必須</span></label>
                <div class="password-field">
                    <input class="form-control @error('password') is-invalid @enderror" id="password" name="password" type="password" minlength="8" autocomplete="new-password" required>
                    <button class="password-toggle" type="button" data-password-toggle="#password" aria-label="パスワードを表示する">表示</button>
                </div>
                @error('password')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="password_confirmation">新しいパスワード確認 <span class="required-label">必須</span></label>
                <div class="password-field">
                    <input class="form-control" id="password_confirmation" name="password_confirmation" type="password" minlength="8" autocomplete="new-password" required>
                    <button class="password-toggle" type="button" data-password-toggle="#password_confirmation" aria-label="パスワード確認を表示する">表示</button>
                </div>
            </div>

            <button class="button button--primary button--block" type="submit">パスワードを更新する</button>
        </form>
    </section>
@endsection
