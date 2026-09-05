@extends('layouts.app')

@section('title', 'お手伝いの記録 | おこづかい')

@section('content')
<div class="page-heading page-heading--with-action">
    <div>
        <p class="page-heading__eyebrow">CHORE PERFORMANCE</p>
        <h1><i class="fa-solid fa-file-pen"></i>お手伝いの記録</h1>
        <div class="page-profile">
            <img class="child-card__avatar" src="{{ $child->profile_image ? asset('storage/'.$child->profile_image) : asset('images/default-profile.svg') }}" alt="{{ $child->name }}のプロフィール画像">
            <p class="page-heading__description">
                <span class="child-name">{{ $child->name }}</span>さんのお手伝いの記録を行います。
            </p>
        </div>

    </div>
    <a class="button button--secondary" href="{{ route('parent.children.show', $child) }}">お子様管理へ戻る</a>
</div>

@if ($chores->isEmpty())
<section class="empty-state">
    <span class="empty-state__icon" aria-hidden="true">🧹</span>
    <h2>お手伝い金額設定がありません</h2>
    <p>先にお手伝い金額設定を記録してください。</p>
    <a class="button button--primary" href="{{ route('parent.chores-setting.index') }}">お手伝い金額設定へ</a>
</section>
@else
<section class="form-card">
    <form method="POST" action="{{ route('parent.chores.performance.store', $child) }}" data-loading data-loading-message="記録しています..." novalidate>
        @csrf

        <div class="form-group">
            <label class="form-label" for="chore_id">お手伝いの内容 <span class="required-label">必須</span></label>
            <select class="form-control @error('chore_id') is-invalid @enderror" id="chore_id" name="chore_id" data-chore-select required>
                <option value="">選択してください</option>
                @foreach ($chores as $chore)
                <option value="{{ $chore->id }}" data-reward-amount="{{ $chore->reward_amount }}" @selected((string) old('chore_id')===(string) $chore->id)>
                    {{ $chore->chore_name }}（{{ number_format($chore->reward_amount) }}円）
                </option>
                @endforeach
            </select>
            @error('chore_id')
            <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="reward_amount">おこづかい金額 <span class="required-label">必須</span></label>
            <div class="amount-field">
                <input class="form-control @error('reward_amount') is-invalid @enderror" id="reward_amount" name="reward_amount" type="number" value="{{ old('reward_amount') }}" min="1" step="1" inputmode="numeric" data-chore-reward required>
                <span>円</span>
            </div>
            <p class="form-help">お手伝いの種類を選ぶと、設定済みのおこづかい金額が自動入力されます。</p>
            @error('reward_amount')
            <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        {{--<div class="form-group">
            <label class="form-label" for="performed_at">実施日 <span class="required-label">必須</span></label>
            <input class="form-control @error('performed_at') is-invalid @enderror" id="performed_at" name="performed_at" type="date" value="{{ old('performed_at', now()->toDateString()) }}" required>
        @error('performed_at')
        <p class="field-error">{{ $message }}</p>
        @enderror
        </div>--}}

        <button class="button button--primary button--block" type="submit">お手伝いを記録する</button>
    </form>
</section>
@endif
@endsection