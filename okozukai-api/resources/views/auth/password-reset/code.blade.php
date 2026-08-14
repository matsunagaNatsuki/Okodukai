@extends('layouts.app')

@section('title', '確認コード入力 | おこづかい')

@section('content')
    <section class="auth-card">
        <div class="page-heading auth-heading">
            <p class="page-heading__eyebrow">PASSWORD RESET 2/4</p>
            <h1>確認コード入力</h1>
            <p class="auth-heading__description">メールに届いた4桁の確認コードを入力してください。有効期限は10分です。</p>
        </div>
        <form method="POST" action="{{ route('password-reset.code.verify') }}" data-loading data-loading-message="確認しています...">
            @csrf
            <div class="form-group">
                <label class="form-label" for="code">4桁の確認コード <span class="required-label">必須</span></label>
                <input class="form-control @error('code') is-invalid @enderror" id="code" name="code" type="text" value="{{ old('code') }}" inputmode="numeric" pattern="[0-9]{4}" minlength="4" maxlength="4" autocomplete="one-time-code" required autofocus>
                @error('code')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <button class="button button--primary button--block" type="submit">コードを確認</button>
        </form>
        <p class="auth-link"><a href="{{ route('password-reset.request') }}">メールアドレス入力からやり直す</a></p>
    </section>
@endsection
