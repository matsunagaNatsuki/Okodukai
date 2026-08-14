@extends('layouts.app')

@section('title', '支出履歴 | おこづかい')

@section('content')
    <div class="page-heading page-heading--with-action">
        <div>
            <p class="page-heading__eyebrow">PAYMENT HISTORY</p>
            <h1>支出履歴</h1>
            <p class="page-heading__description">これまでに使ったおこづかいを確認できます。</p>
        </div>
        <a class="button button--primary" href="{{ route('child.payment-record.create') }}">つかったものを記録</a>
    </div>

    <section class="balance-card" aria-label="現在残高">
        <span class="balance-card__label">現在のおこづかい</span>
        <strong class="balance-card__amount">{{ number_format($currentBalance) }}円</strong>
    </section>

    @if ($transactions->isEmpty())
        <section class="empty-state">
            <span class="empty-state__icon" aria-hidden="true">🧾</span>
            <h2>まだ支出履歴がありません</h2>
            <p>おこづかいを使ったら記録してみよう。</p>
            <a class="button button--primary" href="{{ route('child.payment-record.create') }}">つかったものを記録する</a>
        </section>
    @else
        <div class="history-list">
            @foreach ($transactions as $transaction)
                @php($transactionDate = $transaction->transaction_date ?? $transaction->created_at)
                <article class="history-card">
                    <div class="history-card__summary">
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
