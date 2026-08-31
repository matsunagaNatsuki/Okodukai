@extends('layouts.app')

@section('title', 'お手伝いの記録 | おこづかい')

@section('content')
<div class="page-heading">
    <p class="page-heading__eyebrow">CHORE HISTORY</p>
    <h1><i class="fa-solid fa-broom"></i>お手伝いの記録</h1>
    <p class="page-heading__description">これまでにがんばったお手伝いを確認できます。</p>
</div>

@if ($records->isEmpty())
<section class="empty-state">
    <span class="empty-state__icon" aria-hidden="true">🧹</span>
    <h2>まだ行なったお手伝いがありません</h2>
    <p>お手伝いをすると、ここにこれまでがんばったお手伝いの記録が表示されます。</p>
</section>
@else
<div class="history-list">
    @foreach ($records as $record)
    <article class="history-card">
        <div class="history-card__summary">
            {{--<time datetime="{{ $record->performed_at->toDateString() }}">{{ $record->performed_at->format('Y年n月j日') }}</time>--}}
            <time datetime="{{ $record->created_at->toDateString() }}">{{ $record->created_at->format('Y年n月j日') }}</time>
            <strong>{{ $record->chore?->chore_name ?? '削除済みのお手伝い' }}</strong>
            <span class="history-card__amount">+{{ number_format($record->reward_amount) }}円</span>
        </div>
    </article>
    @endforeach
</div>

<div class="pagination-wrapper">
    {{ $records->links() }}
</div>
@endif
@endsection