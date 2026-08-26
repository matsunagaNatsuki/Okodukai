@extends('layouts.app')

@section('title', 'ホーム | おこづかい')

@section('content')
<section class="child-home-hero">
    <img class="child-home-avatar" src="{{ $child->profile_image ? asset('storage/'.$child->profile_image) : asset('images/default-profile.svg') }}" alt="{{ $child->name }}のプロフィール画像">
    <div class="child-home-greeting">
        <span>こんにちは！</span>
        <h1>{{ $child->name }}さん</h1>
    </div>
    <div class="child-balance">
        <span>現在のおこづかい</span>
        <strong>{{ number_format($currentBalance) }}円</strong>
    </div>
</section>

{{--<nav class="child-menu-grid" aria-label="子ども用メニュー">
        <a href="{{ route('child.payment-record.create') }}"><span aria-hidden="true">✏️</span><strong>つかったものを記録</strong></a>
<a href="{{ route('child.payment-history.index') }}"><span aria-hidden="true">🧾</span><strong>支出履歴</strong></a>
<a href="{{ route('child.chores.history') }}"><span aria-hidden="true">🧹</span><strong>お手伝い履歴</strong></a>
<a href="{{ route('child.savings.show') }}"><span aria-hidden="true">🎯</span><strong>貯金目標</strong></a>
<a href="{{ route('child.profile.edit') }}"><span aria-hidden="true">😊</span><strong>プロフィール</strong></a>
<form method="POST" action="{{ route('child.logout') }}" data-loading data-loading-message="ログアウトしています...">
    @csrf
    <button type="submit"><span aria-hidden="true">🚪</span><strong>ログアウト</strong></button>
</form>
</nav>--}}

<div class="child-home-columns">
    <section class="home-panel">
        <div class="home-panel__heading">
            <h2>お金をためよう！</h2>
            <a href="{{ route('child.savings.show') }}">くわしく見る</a>
        </div>
        @if ($savingGoal)
        <h3 class="home-goal-name">{{ $savingGoal->item_name }}</h3>
        <div class="saving-stat-grid saving-stat-grid--home">
            <div class="saving-stat"><span>ためたいお金</span><strong>{{ number_format($savingGoal->target_amount) }}円</strong></div>
            <div class="saving-stat"><span>あといくら？</span><strong>{{ number_format($remainingAmount) }}円</strong></div>
            <div class="saving-stat"><span>ゴールまで</span><strong>{{ number_format($achievementRate, 1) }}%</strong></div>
        </div>
        <div class="goal-progress" role="progressbar" aria-label="貯金達成率" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $progressRate }}">
            <span class="goal-progress__bar" style="width: {{ $progressRate }}%"></span>
        </div>
        @if ($currentBalance >= $savingGoal->target_amount)<p class="home-goal-achieved">やったね！目標クリア！🎉</p>@endif
        @else
        <div class="home-empty">
            <p>まだためたいお金の設定がありません。</p><a class="button button--primary button--small" href="{{ route('child.savings.show') }}">ためたいお金を設定する</a>
        </div>
        @endif
    </section>

    <section class="home-panel">
        <div class="home-panel__heading">
            <h2>おこづかいの記録</h2><a href="{{ route('child.payment-history.index') }}">最近使ったお金を見る</a>
        </div>
        @if ($recentTransactions->isEmpty())
        <div class="home-empty">
            <p>まだおこづかいの記録がありません。</p>
        </div>
        @else
        <div class="recent-transaction-list">
            @foreach ($recentTransactions as $transaction)
            <div class="recent-transaction">
                @php($transactionDate = $transaction->transaction_date ?? $transaction->created_at)
                <div><strong>{{ $transaction->title }}</strong><time datetime="{{ $transactionDate->toDateString() }}">{{ $transactionDate->format('n月j日') }}</time></div>
                <span class="{{ $transaction->type === 'income' ? 'is-income' : 'is-expense' }}">{{ $transaction->type === 'income' ? '+' : '-' }}{{ number_format($transaction->amount) }}円</span>
            </div>
            @endforeach
        </div>
        @endif
    </section>
</div>
@endsection