@extends('layouts.app')

@section('title', '貯金目標 | おこづかい')

@section('content')
    <div class="page-heading page-heading--with-action">
        <div>
            <p class="page-heading__eyebrow">SAVING GOAL</p>
            <h1>貯金目標</h1>
            <p class="page-heading__description">欲しいものを決めて、楽しく貯金しよう。</p>
        </div>
        <a class="button button--secondary" href="{{ route('child.home') }}">ホームへ戻る</a>
    </div>

    @if ($savingGoal !== null)
        <section class="saving-goal-card">
            @if ($achievementRate >= 100)
                <div class="goal-achieved" role="status"><span aria-hidden="true">🎉</span> 目標達成！</div>
            @endif

            <div class="saving-goal-item">
                <span>欲しいもの</span>
                <strong>{{ $savingGoal->item_name }}</strong>
            </div>
            <div class="saving-stat-grid">
                <div class="saving-stat"><span>目標金額</span><strong>{{ number_format($savingGoal->target_amount) }}円</strong></div>
                <div class="saving-stat"><span>現在残高</span><strong>{{ number_format($currentBalance) }}円</strong></div>
                <div class="saving-stat"><span>目標まであと</span><strong>{{ number_format($remainingAmount) }}円</strong></div>
                <div class="saving-stat"><span>達成率</span><strong>{{ number_format($achievementRate, 1) }}%</strong></div>
            </div>
            <div class="goal-progress-section">
                <div class="goal-progress-label"><span>貯金の進み具合</span><span>{{ number_format($progressRate, 1) }}%</span></div>
                <div class="goal-progress" role="progressbar" aria-label="貯金達成率" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $progressRate }}">
                    <span class="goal-progress__bar" style="width: {{ $progressRate }}%"></span>
                </div>
            </div>
        </section>
    @else
        <section class="empty-state">
            <span class="empty-state__icon" aria-hidden="true">🎯</span>
            <h2>貯金目標はまだありません</h2>
            <p>欲しいものと目標金額を登録してみよう。</p>
        </section>
    @endif

    <section class="form-card">
        <h2>{{ $savingGoal === null ? '貯金目標を登録' : '貯金目標を編集' }}</h2>
        <form method="POST" action="{{ route('child.savings.store') }}" data-loading data-loading-message="保存しています..." novalidate>
            @csrf
            <div class="form-group">
                <label class="form-label" for="item_name">
                    欲しいもの
                    <span class="required-label">必須</span>
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
                    目標金額
                    <span class="required-label">必須</span>
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
                {{ $savingGoal === null ? '登録する' : '更新する' }}
            </button>
        </form>
    </section>
@endsection
