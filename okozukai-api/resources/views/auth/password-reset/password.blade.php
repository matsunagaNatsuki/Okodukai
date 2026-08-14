@extends('layouts.app')

@section('title', '新しいパスワード | おこづかい')

@section('content')
    <section class="auth-card">
        <div class="page-heading auth-heading">
            <p class="page-heading__eyebrow">PASSWORD RESET 4/4</p>
            <h1>新しいパスワード</h1>
            <p class="auth-heading__description">{{ $target->name }}さんの新しいパスワードを設定してください。</p>
        </div>
        <form method="POST" action="{{ route('password-reset.password.update') }}" data-loading data-loading-message="更新しています...">
            @csrf
            <div class="form-group">
                <label class="form-label" for="password">新しいパスワード <span class="required-label">必須</span></label>
                <div class="password-field">
                    <input class="form-control @error('password') is-invalid @enderror" id="password" name="password" type="password" minlength="8" autocomplete="new-password" required autofocus>
                    <button class="password-toggle" type="button" data-password-toggle="#password" aria-label="パスワードを表示する">表示</button>
                </div>
                @error('password')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="password_confirmation">新しいパスワード確認 <span class="required-label">必須</span></label>
                <div class="password-field">
                    <input class="form-control" id="password_confirmation" name="password_confirmation" type="password" minlength="8" autocomplete="new-password" required>
                    <button class="password-toggle" type="button" data-password-toggle="#password_confirmation" aria-label="パスワード確認を表示する">表示</button>
                </div>
            </div>
            <button class="button button--primary button--block" type="submit">パスワードを再設定</button>
        </form>
    </section>
@endsection
