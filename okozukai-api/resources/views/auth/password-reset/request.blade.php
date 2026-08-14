@extends('layouts.app')

@section('title', 'パスワード再設定 | おこづかい')

@section('content')
    <section class="auth-card">
        <div class="page-heading auth-heading">
            <p class="page-heading__eyebrow">PASSWORD RESET 1/4</p>
            <h1>パスワード再設定</h1>
            <p class="auth-heading__description">家族コードを作成した代表保護者のメールアドレスを入力してください。</p>
        </div>
        <form method="POST" action="{{ route('password-reset.send') }}" data-loading data-loading-message="送信しています...">
            @csrf
            <div class="form-group">
                <label class="form-label" for="email">代表保護者のメールアドレス <span class="required-label">必須</span></label>
                <input class="form-control @error('email') is-invalid @enderror" id="email" name="email" type="email" value="{{ old('email') }}" maxlength="255" autocomplete="email" required autofocus>
                @error('email')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <button class="button button--primary button--block" type="submit">確認コードを送信</button>
        </form>
        <p class="auth-link"><a href="{{ route('parent.login') }}">保護者ログインへ戻る</a></p>
    </section>
@endsection
