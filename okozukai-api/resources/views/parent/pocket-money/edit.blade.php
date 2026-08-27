@extends('layouts.app')

@section('title', '定期おこづかい設定 | おこづかい')

@section('content')
    <div class="page-heading page-heading--with-action">
        <div>
            <p class="page-heading__eyebrow">MONTHLY ALLOWANCE</p>
            <h1>おこづかい入金</h1>
            <p class="page-heading__description">{{ $child->name }}さんの毎月のおこづかいを設定します。</p>
        </div>
        <a class="button button--secondary" href="{{ route('parent.children.show', $child) }}">お子様管理へ戻る</a>
    </div>

    <section class="form-card">
        <form method="POST" action="{{ route('parent.pocket-money.update', $child) }}" data-loading data-loading-message="保存しています..." novalidate>
            @csrf
            @method('PUT')

            <div class="form-group">
                <label class="form-label" for="amount">おこづかい金額 <span class="required-label">必須</span></label>
                <div class="amount-field">
                    <input
                        class="form-control @error('amount') is-invalid @enderror"
                        id="amount"
                        name="amount"
                        type="number"
                        value="{{ old('amount', $allowance?->amount) }}"
                        min="1"
                        step="1"
                        inputmode="numeric"
                        required
                    >
                    <span>円</span>
                </div>
                @error('amount')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="payment_day">支給日 <span class="required-label">必須</span></label>
                <select class="form-control @error('payment_day') is-invalid @enderror" id="payment_day" name="payment_day" required>
                    <option value="">選択してください</option>
                    @for ($day = 1; $day <= 31; $day++)
                        <option value="{{ $day }}" @selected((string) old('payment_day', $allowance?->payment_day) === (string) $day)>{{ $day }}日</option>
                    @endfor
                </select>
                <p class="form-help">支給日が存在しない月の扱いは、定期入金処理を実装する際に月末支給として統一します。</p>
                @error('payment_day')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            {{--<fieldset class="form-group status-fieldset">
                <legend class="form-label">設定状態 <span class="required-label">必須</span></legend>
                @php($activeValue = (string) old('is_active', $allowance?->is_active ?? true))
                <div class="status-options">
                    <label class="status-option">
                        <input name="is_active" type="radio" value="1" @checked($activeValue === '1')>
                        <span><strong>有効</strong><small>毎月のおこづかい対象にする</small></span>
                    </label>
                    <label class="status-option">
                        <input name="is_active" type="radio" value="0" @checked($activeValue === '0')>
                        <span><strong>無効</strong><small>定期おこづかいを停止する</small></span>
                    </label>
                </div>
                @error('is_active')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </fieldset>--}}

            <button class="button button--primary button--block" type="submit">
                {{ $allowance ? '設定を更新する' : '設定を登録する' }}
            </button>
        </form>
    </section>
@endsection
