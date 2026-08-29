@extends('layouts.app')

@section('title', '子どもの貯金目標 | おこづかい')

@section('content')
<div class="page-heading page-heading--with-action">
    <div>
        <p class="page-heading__eyebrow">SAVING GOAL</p>
        <h1><i class="fa-solid fa-sack-dollar"></i>貯金の目標</h1>
        <p class="page-heading__description">{{ $child->name }}さんが設定した目標を確認できます。</p>
    </div>
    <a class="button button--secondary" href="{{ route('parent.children.show', $child) }}">お子様管理へ戻る</a>
</div>

@if ($savingGoal === null)
<section class="empty-state">
    <span class="empty-state__icon" aria-hidden="true"></span>
    <h2>お子様の貯金の目標はまだ設定されていません</h2>
    <p>{{ $child->name }}さんが貯金の目標を設定すると、ここに表示されます。</p>
</section>
@else
<section class="saving-goal-card">
    {{-- 現在残高が目標金額より高い時 --}}
    @if ($currentBalance >= $savingGoal->target_amount)
    <div class="goal-achieved" role="status">
        <span aria-hidden="true">🎉</span>
        目標達成！
    </div>
    @endif

    <div class="saving-goal-item">
        <span>欲しいもの</span>
        <strong>{{ $savingGoal->item_name }}</strong>
    </div>

    <div class="saving-stat-grid">
        <div class="saving-stat">
            <span>目標金額</span>
            <strong>{{ number_format($savingGoal->target_amount) }}円</strong>
        </div>
        <div class="saving-stat">
            <span>現在残高</span>
            <strong>{{ number_format($currentBalance) }}円</strong>
        </div>
        <div class="saving-stat">
            <span>目標まであと</span>
            <strong>{{ number_format($remainingAmount) }}円</strong>
        </div>
        <div class="saving-stat">
            <span>達成率</span>
            <strong>{{ number_format($achievementRate, 1) }}%</strong>
        </div>
    </div>

    <div class="goal-progress-section">
        <div class="goal-progress-label">
            <span>貯金の進み具合</span>
            <span>{{ number_format($progressRate, 1) }}%</span>
        </div>
        <div class="goal-progress" role="progressbar" aria-label="貯金達成率" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $progressRate }}">
            <span class="goal-progress__bar" style="width: {{ $progressRate }}%"></span>
        </div>
    </div>

    <p class="readonly-note">この画面は確認専用です。貯金目標の登録・変更はお子様画面から行います。</p>
</section>
@endif
@endsection