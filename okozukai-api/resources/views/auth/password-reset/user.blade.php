@extends('layouts.app')

@section('title', 'ユーザー選択 | おこづかい')

@section('content')
    <section class="auth-card">
        <div class="page-heading auth-heading">
            <p class="page-heading__eyebrow">PASSWORD RESET 3/4</p>
            <h1>対象ユーザーを選択</h1>
            <p class="auth-heading__description">パスワードを変更する家族アカウントを選択してください。</p>
        </div>
        <form method="POST" action="{{ route('password-reset.user.select') }}" data-loading data-loading-message="確認しています...">
            @csrf
            <div class="form-group">
                <label class="form-label" for="user_id">対象ユーザー <span class="required-label">必須</span></label>
                <select class="form-control @error('user_id') is-invalid @enderror" id="user_id" name="user_id" required autofocus>
                    <option value="">選択してください</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" @selected((string) old('user_id') === (string) $user->id)>{{ $user->name }}（{{ $user->role === 'parent' ? '保護者' : '子ども' }}）</option>
                    @endforeach
                </select>
                @error('user_id')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <button class="button button--primary button--block" type="submit">このユーザーのパスワードを変更</button>
        </form>
    </section>
@endsection
