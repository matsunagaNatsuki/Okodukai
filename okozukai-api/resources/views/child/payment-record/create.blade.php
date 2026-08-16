@extends('layouts.app')

@section('title', 'つかったものを記録 | おこづかい')

@section('content')
<div class="page-heading page-heading--with-action">
    <div>
        <p class="page-heading__eyebrow">PAYMENT RECORD</p>
        <h1>つかったものを記録</h1>
        <p class="page-heading__description">何にいくら使ったか記録しよう。</p>
    </div>
    <a class="button button--secondary" href="{{ route('child.home') }}">ホームへ戻る</a>
</div>

<section class="balance-card" aria-label="現在残高">
    <span class="balance-card__label">現在のおこづかい</span>
    <strong class="balance-card__amount">{{ number_format($currentBalance) }}円</strong>
</section>

<section class="form-card">
    <form method="POST" action="{{ route('child.payment-record.store') }}" data-loading data-loading-message="登録しています..." novalidate>
        @csrf
        <div class="form-group">
            <label class="form-label" for="title">
                使った内容
                <span class="required-label">必須</span>
            </label>
            <input class="form-control @error('title') is-invalid @enderror" id="title" name="title" type="text" value="{{ old('title') }}" maxlength="255" required autofocus>
            @error('title')
            <p class="field-error">
                {{ $message }}
            </p>
            @enderror
        </div>
        <div class="form-group">
            <label class="form-label" for="amount">
                金額
                <span class="required-label">必須</span>
            </label>
            <div class="amount-field">
                <input class="form-control @error('amount') is-invalid @enderror" id="amount" name="amount" type="number" value="{{ old('amount') }}" min="1" max="{{ max($currentBalance, 0) }}" step="1" inputmode="numeric" required>
                <span>円</span>
            </div>
            <p class="form-help">現在残高以内の金額を入力してください。</p>
            @error('amount')
            <p class="field-error">
                {{ $message }}
            </p>
            @enderror
        </div>
        <div class="form-group">
            <label class="form-label" for="used_at">
                使用日
                <span class="required-label">必須</span>
            </label>
            <input class="form-control @error('used_at') is-invalid @enderror" id="used_at" name="used_at" type="date" value="{{ old('used_at', now()->toDateString()) }}" required>
            @error('used_at')
            <p class="field-error">
                {{ $message }}
            </p>
            @enderror
        </div>
        <button class="button button--primary button--block" type="submit" @disabled($currentBalance < 1)>記録する</button>
        {{-- 現在残高が0の時は記録しない--}}
        @if ($currentBalance < 1)
            <p class="field-error payment-balance-warning">残高がないため、支出を登録できません。</p>
            @endif
    </form>
</section>
@endsection