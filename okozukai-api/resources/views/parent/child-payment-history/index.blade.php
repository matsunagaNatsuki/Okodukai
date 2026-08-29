@extends('layouts.app')

@section('title', 'お子様が使用したお金 | おこづかい')

@section('content')
<div class="page-heading page-heading--with-action">
    <div>
        <p class="page-heading__eyebrow">PAYMENT HISTORY</p>
        <h1><i class="fa-solid fa-cart-shopping"></i>お子様が使ったお金</h1>
        <p class="page-heading__description">{{ $child->name }}さんが使用したおこづかいの履歴です。</p>
    </div>
    <a class="button button--secondary" href="{{ route('parent.children.show', $child) }}">お子様管理へ戻る</a>
</div>

<section class="balance-card" aria-label="現在残高">
    <span class="balance-card__label">{{ $child->name }}さんの現在残高</span>
    <strong class="balance-card__amount">{{ number_format($currentBalance) }}円</strong>
</section>

@if ($transactions->isEmpty())
<section class="empty-state">
    <span class="empty-state__icon" aria-hidden="true">🧾</span>
    <h2>まだお子様が使用したお金がありません</h2>
    <p>お子様がおこづかいを使用すると、ここに履歴が表示されます。</p>
</section>
@else
<div class="history-list">
    @foreach ($transactions as $transaction)
    <article class="history-card">
        <div class="history-card__summary">
            @php($transactionDate = $transaction->transaction_date ?? $transaction->created_at)
            <time datetime="{{ $transactionDate->toDateString() }}">{{ $transactionDate->format('Y年n月j日') }}</time>
            <strong>{{ $transaction->title }}</strong>
            <span class="history-card__amount history-card__amount--expense">-{{ number_format($transaction->amount) }}円</span>
        </div>
    </article>
    @endforeach
</div>

<div class="pagination-wrapper">
    {{ $transactions->links() }}
</div>
@endif
@endsection