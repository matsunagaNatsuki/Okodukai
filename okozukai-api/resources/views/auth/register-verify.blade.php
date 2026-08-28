<!-- @extends('layouts.app') -->

@section('title', '確認コード入力 | おこづかい')

@section('content')
<section class="auth-card">
    <div class="page-heading auth-heading">
        <p class="page-heading__eyebrow">PARENT SIGN UP</p>
        <h1>確認コードを入力してください</h1>
        <p class="auth-heading__description">
            {{ $email }} に確認コードを送信しました。
        </p>
    </div>

    <form method="POST" action="{{ route('parent.register.verify.store', ['token' => $token]) }}" data-loading data-loading-message="確認しています..." novalidate>
        @csrf

        <div class="form-group">
            <label class="form-label" for="code">
                確認コード
                <span class="required-label">必須</span>
            </label>

            <input
                class="form-control @error('code') is-invalid @enderror"
                id="code"
                name="code"
                type="text"
                value="{{ old('code') }}"
                inputmode="numeric"
                autocomplete="one-time-code"
                required
                autofocus>

            @error('code')
            <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <button class="button button--primary button--block" type="submit">
            確認して登録する
        </button>
    </form>

    <p class="auth-link">
        <a href="{{ route('parent.register') }}">
            新規登録画面へ戻る
        </a>
    </p>
</section>
@endsection