@extends('layouts.app')

@section('title', 'お手伝い報酬設定 | おこづかい')

@section('content')
    <div class="page-heading">
        <p class="page-heading__eyebrow">CHORE SETTINGS</p>
        <h1>お手伝い報酬設定</h1>
        <p class="page-heading__description">家族で使うお手伝いと報酬金額を設定します。</p>
    </div>

    <div class="settings-layout">
        <section class="form-card settings-create-card">
            <h2 class="section-title">新しいお手伝い</h2>
            <form method="POST" action="{{ route('parent.chores-setting.store') }}" data-loading data-loading-message="登録しています...">
                @csrf

                <div class="form-group">
                    <label class="form-label" for="chore_name">お手伝い名 <span class="required-label">必須</span></label>
                    <input class="form-control @error('chore_name') is-invalid @enderror" id="chore_name" name="chore_name" type="text" value="{{ old('chore_name') }}" maxlength="100" required>
                    @error('chore_name')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="reward_amount">報酬金額 <span class="required-label">必須</span></label>
                    <div class="amount-field">
                        <input class="form-control @error('reward_amount') is-invalid @enderror" id="reward_amount" name="reward_amount" type="number" value="{{ old('reward_amount') }}" min="1" step="1" inputmode="numeric" required>
                        <span>円</span>
                    </div>
                    @error('reward_amount')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <button class="button button--primary button--block" type="submit">登録する</button>
            </form>
        </section>

        <section class="settings-list-section">
            <h2 class="section-title">登録済みのお手伝い</h2>
            <div class="ajax-message" data-ajax-message aria-live="polite"></div>

            @if ($chores->isEmpty())
                <div class="empty-state empty-state--compact" data-empty-chores>
                    <span class="empty-state__icon" aria-hidden="true">🧹</span>
                    <p>まだお手伝い設定がありません</p>
                </div>
            @else
                <div class="chore-setting-list">
                    @foreach ($chores as $chore)
                        <article class="chore-setting-card" data-chore-card="{{ $chore->id }}">
                            <div class="chore-setting-display" data-chore-display>
                                <div>
                                    <h3 data-chore-name>{{ $chore->chore_name }}</h3>
                                    <p data-chore-reward>{{ number_format($chore->reward_amount) }}円</p>
                                </div>
                                <div class="card-actions">
                                    <button class="button button--secondary button--small" type="button" data-chore-edit>編集</button>
                                    <button
                                        class="button button--danger button--small"
                                        type="button"
                                        data-ajax-delete
                                        data-delete-url="{{ route('parent.chores-setting.destroy', $chore) }}"
                                        data-delete-target="[data-chore-card]"
                                        data-delete-title="お手伝い設定の削除"
                                        data-delete-message="「{{ $chore->chore_name }}」を削除しますか？"
                                    >削除</button>
                                </div>
                            </div>

                            <form class="chore-edit-form" action="{{ route('parent.chores-setting.update', $chore) }}" method="POST" data-chore-edit-form hidden>
                                @csrf
                                @method('PUT')
                                <div class="form-group">
                                    <label class="form-label" for="chore-name-{{ $chore->id }}">お手伝い名</label>
                                    <input class="form-control" id="chore-name-{{ $chore->id }}" name="chore_name" type="text" value="{{ $chore->chore_name }}" maxlength="100" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="chore-reward-{{ $chore->id }}">報酬金額</label>
                                    <div class="amount-field">
                                        <input class="form-control" id="chore-reward-{{ $chore->id }}" name="reward_amount" type="number" value="{{ $chore->reward_amount }}" min="1" step="1" inputmode="numeric" required>
                                        <span>円</span>
                                    </div>
                                </div>
                                <div class="field-error ajax-field-errors" data-edit-errors role="alert"></div>
                                <div class="card-actions">
                                    <button class="button button--secondary button--small" type="button" data-chore-cancel>キャンセル</button>
                                    <button class="button button--primary button--small" type="submit">保存</button>
                                </div>
                            </form>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
@endsection
