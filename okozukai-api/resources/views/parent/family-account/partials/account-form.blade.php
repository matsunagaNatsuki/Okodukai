{{-- 家族アカウントの追加、編集の入力フォーム --}}

@php($editing = $editing ?? false)
@php($account = $account ?? null)
@php($isCurrentForm = old('form_context') === $prefix)
<input type="hidden" name="form_context" value="{{ $prefix }}">
<div class="account-form-grid">
    <div class="form-group">
        <label class="form-label" for="{{ $prefix }}-name">
            名前
            <span class="required-label">必須</span>
        </label>
        <input class="form-control {{ $isCurrentForm && $errors->has('name') ? 'is-invalid' : '' }}" id="{{ $prefix }}-name" name="name" type="text" value="{{ $isCurrentForm ? old('name') : ($editing ? $account->name : '') }}" maxlength="100" required>
        @if ($isCurrentForm)
            @error('name')
            <p class="field-error">
                {{ $message }}
            </p>
            @enderror
        @endif
    </div>
    @if ($type === 'parent')
        <div class="form-group">
            <label class="form-label" for="{{ $prefix }}-email">
                メールアドレス
                <span class="required-label">必須</span>
            </label>
            <input class="form-control {{ $isCurrentForm && $errors->has('email') ? 'is-invalid' : '' }}" id="{{ $prefix }}-email" name="email" type="email" value="{{ $isCurrentForm ? old('email') : ($editing ? $account->email : '') }}" maxlength="255" required>
            @if ($isCurrentForm)
                @error('email')
                <p class="field-error">
                    {{ $message }}
                </p>
                @enderror
            @endif
        </div>
    @else
        <div class="form-group">
            <label class="form-label" for="{{ $prefix }}-login-id">
                ログインID
                <span class="required-label">必須</span>
            </label>
            <input class="form-control {{ $isCurrentForm && $errors->has('login_id') ? 'is-invalid' : '' }}" id="{{ $prefix }}-login-id" name="login_id" type="text" value="{{ $isCurrentForm ? old('login_id') : ($editing ? $account->login_id : '') }}" maxlength="100" required>
            @if ($isCurrentForm)
                @error('login_id')
                <p class="field-error">
                    {{ $message }}
                </p>
                @enderror
            @endif
        </div>
    @endif
    <div class="form-group">
        <label class="form-label" for="{{ $prefix }}-password">
            パスワード
            @unless($editing)
                <span class="required-label">必須</span>
            @endunless
        </label>
        <input class="form-control {{ $isCurrentForm && $errors->has('password') ? 'is-invalid' : '' }}" id="{{ $prefix }}-password" name="password" type="password" minlength="8" maxlength="15" autocomplete="new-password" @required(!$editing)>
        <p class="form-help">パスワードは8文字以上15文字以下で入力してください。</p>
        @if ($editing)
        <p class="form-help">変更しない場合は空欄のままにしてください。</p>
        @endif
        @if ($isCurrentForm)
            @error('password')
            <p class="field-error">
                {{ $message }}
            </p>
            @enderror
        @endif
    </div>
    <div class="form-group">
        <label class="form-label" for="{{ $prefix }}-password-confirmation">パスワード確認</label>
        <input class="form-control" id="{{ $prefix }}-password-confirmation" name="password_confirmation" type="password" minlength="8" maxlength="15" autocomplete="new-password" @required(!$editing)>
    </div>
</div>
