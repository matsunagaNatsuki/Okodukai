@extends('layouts.app')

@section('title', 'ためたいお金 | おこづかい')

@section('content')
<div class="page-heading page-heading--with-action">
    <div>
        <p class="page-heading__eyebrow">SAVING GOAL</p>
        <h1>ためたいお金</h1>
        <p class="page-heading__description">欲しいものを決めて、楽しくお金をためよう！</p>
    </div>
    <a class="button button--secondary" href="{{ route('child.home') }}">ホームへ戻る</a>
</div>

@if ($savingGoal !== null)
<section class="saving-goal-card">
    @if ($achievementRate >= 100)
    {{--<div class="goal-achieved" role="status"><span aria-hidden="true">🎉</span> 目標達成！</div>--}}
    <div class="goal-achieved" role="status">やったね！ぜんぶたまったよ！🎉</div>
    @endif

    <div class="saving-goal-item">
        <span>欲しいもの</span>
        <strong>{{ $savingGoal->item_name }}</strong>
    </div>
    <div class="saving-stat-grid">
        <div class="saving-stat"><span>ためたいお金</span><strong>{{ number_format($savingGoal->target_amount) }}円</strong></div>
        <div class="saving-stat"><span>現在のおこづかい</span><strong>{{ number_format($currentBalance) }}円</strong></div>
        <div class="saving-stat"><span>残りのお金は</span><strong>{{ number_format($remainingAmount) }}円</strong></div>
        <div class="saving-stat"><span>ゴールまで
            </span><strong>{{ number_format($achievementRate, 1) }}%</strong></div>
    </div>
    <div class="goal-progress-section">
        <div class="goal-progress-label"><span>どのぐらいたまったかな？？</span><span>{{ number_format($progressRate, 1) }}%</span></div>
        <div class="goal-progress" role="progressbar" aria-label="貯金達成率" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $progressRate }}">
            <span class="goal-progress__bar" style="width: {{ $progressRate }}%"></span>
        </div>
    </div>
</section>
@else
<section class="empty-state">
    <span class="empty-state__icon" aria-hidden="true">🎯</span>
    <h2>お金の目標はまだありません</h2>
    <p>欲しいものと目標のお金を登録してみよう。</p>
</section>
@endif

<section class="form-card">
    <h2>{{ $savingGoal === null ? 'お金の目標を立てる' : 'お金の目標を変える' }}</h2>
    <form method="POST" action="{{ route('child.savings.store') }}" data-loading data-loading-message="保存してるよ..." novalidate>
        @csrf
        <div class="form-group">
            <label class="form-label" for="item_name">
                欲しいもの
                <span class="required-label">必ず入力してね</span>
            </label>
            <input class="form-control @error('item_name') is-invalid @enderror" id="item_name" name="item_name" type="text" value="{{ old('item_name', $savingGoal?->item_name) }}" maxlength="255" required autofocus>
            @error('item_name')
            <p class="field-error">
                {{ $message }}
            </p>
            @enderror
        </div>
        <div class="form-group">
            <label class="form-label" for="target_amount">
                ためたいお金
                <span class="required-label">必ず入力してね</span>
            </label>
            <div class="amount-field">
                <input class="form-control @error('target_amount') is-invalid @enderror" id="target_amount" name="target_amount" type="number" value="{{ old('target_amount', $savingGoal?->target_amount) }}" min="1" step="10" inputmode="numeric" required>
                <span>円</span>
            </div>
            @error('target_amount')
            <p class="field-error">
                {{ $message }}
            </p>
            @enderror
        </div>
        <button class="button button--primary button--block" type="submit">
            {{ $savingGoal === null ? 'お金の目標を立てる' : 'お金の目標を変える' }}
        </button>
    </form>
</section>
@endsection