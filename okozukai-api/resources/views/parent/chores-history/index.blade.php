@extends('layouts.app')

@section('title', 'お手伝い履歴 | おこづかい')

@section('content')
    <div class="page-heading page-heading--with-action">
        <div>
            <p class="page-heading__eyebrow">CHORE HISTORY</p>
            <h1>お手伝い履歴</h1>
            <p class="page-heading__description">{{ $child->name }}さんのお手伝い実績です。</p>
        </div>
        <a class="button button--primary" href="{{ route('parent.chores.performance', $child) }}">実績を登録</a>
    </div>

    @if ($records->isEmpty())
        <section class="empty-state">
            <span class="empty-state__icon" aria-hidden="true">🧹</span>
            <h2>まだお手伝い履歴がありません</h2>
            <p>お手伝いをしたら実績を登録してください。</p>
        </section>
    @else
        <div class="history-list">
            @foreach ($records as $record)
                <article class="history-card">
                    <div class="history-card__summary">
                        <time datetime="{{ $record->performed_at->toDateString() }}">{{ $record->performed_at->format('Y年n月j日') }}</time>
                        <strong>{{ $record->chore->chore_name }}</strong>
                        <span class="history-card__amount">+{{ number_format($record->reward_amount) }}円</span>
                    </div>

                    <details class="history-editor">
                        <summary>編集・削除</summary>
                        <form method="POST" action="{{ route('parent.chores.history.update', [$child, $record]) }}" data-loading data-loading-message="更新しています...">
                            @csrf
                            @method('PUT')
                            <div class="history-edit-grid">
                                <div class="form-group">
                                    <label class="form-label" for="chore-{{ $record->id }}">お手伝い内容</label>
                                    <select class="form-control" id="chore-{{ $record->id }}" name="chore_id" data-chore-select required>
                                        @foreach ($chores as $chore)
                                            <option value="{{ $chore->id }}" data-reward-amount="{{ $chore->reward_amount }}" @selected($record->chore_id === $chore->id)>{{ $chore->chore_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="reward-{{ $record->id }}">報酬金額</label>
                                    <div class="amount-field">
                                        <input class="form-control" id="reward-{{ $record->id }}" name="reward_amount" type="number" value="{{ $record->reward_amount }}" min="1" step="1" data-chore-reward required>
                                        <span>円</span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="date-{{ $record->id }}">実施日</label>
                                    <input class="form-control" id="date-{{ $record->id }}" name="performed_at" type="date" value="{{ $record->performed_at->toDateString() }}" required>
                                </div>
                            </div>
                            <div class="card-actions">
                                <button class="button button--primary button--small" type="submit">更新する</button>
                            </div>
                        </form>

                        <form method="POST" action="{{ route('parent.chores.history.destroy', [$child, $record]) }}" data-loading data-loading-message="削除しています..." data-confirm-submit="このお手伝い実績と対応する収入を削除しますか？">
                            @csrf
                            @method('DELETE')
                            <button class="button button--danger button--small" type="submit">削除する</button>
                        </form>
                    </details>
                </article>
            @endforeach
        </div>

        <div class="pagination-wrapper">
            {{ $records->links() }}
        </div>
    @endif
@endsection
