{{-- 家族アカウントの追加、編集の入力フォーム --}}

@php($editing = $editing ?? false)
@php($account = $account ?? null)
<div class="account-form-grid">
    <div class="form-group">
        <label class="form-label" for="{{ $prefix }}-name">
            名前
            <span class="required-label">必須</span>
        </label>
        <input class="form-control @error('name') is-invalid @enderror" id="{{ $prefix }}-name" name="name" type="text" value="{{ $editing ? $account->name : old('name') }}" maxlength="100" required>
        @error('name')
        <p class="field-error">
            {{ $message }}
        </p>
        @enderror
    </div>
    @if ($type === 'parent')
        <div class="form-group">
            <label class="form-label" for="{{ $prefix }}-email">
                メールアドレス
                <span class="required-label">必須</span>
            </label>
            <input class="form-control @error('email') is-invalid @enderror" id="{{ $prefix }}-email" name="email" type="email" value="{{ $editing ? $account->email : old('email') }}" maxlength="255" required>
            @error('email')
            <p class="field-error">
                {{ $message }}
            </p>
            @enderror
        </div>
    @else
        <div class="form-group">
            <label class="form-label" for="{{ $prefix }}-login-id">
                ログインID
                <span class="required-label">必須</span>
            </label>
            <input class="form-control @error('login_id') is-invalid @enderror" id="{{ $prefix }}-login-id" name="login_id" type="text" value="{{ $editing ? $account->login_id : old('login_id') }}" maxlength="100" required>
            @error('login_id')
            <p class="field-error">
                {{ $message }}
            </p>
            @enderror
        </div>
    @endif
    <div class="form-group">
        <label class="form-label" for="{{ $prefix }}-password">
            パスワード
            @unless($editing)
                <span class="required-label">必須</span>
            @endunless
        </label>
        <input class="form-control @error('password') is-invalid @enderror" id="{{ $prefix }}-password" name="password" type="password" minlength="8" maxlength="15" autocomplete="new-password" @required(!$editing)>
        <p class="form-help">パスワードは8文字以上15文字以下で入力してください。</p>
        @if ($editing)
        <p class="form-help">変更しない場合は空欄のままにしてください。</p>
        @endif
        @error('password')
        <p class="field-error">
            {{ $message }}
        </p>
        @enderror
    </div>
    <div class="form-group">
        <label class="form-label" for="{{ $prefix }}-password-confirmation">パスワード確認</label>
        <input class="form-control" id="{{ $prefix }}-password-confirmation" name="password_confirmation" type="password" minlength="8" maxlength="15" autocomplete="new-password" @required(!$editing)>
    </div>
</div>
