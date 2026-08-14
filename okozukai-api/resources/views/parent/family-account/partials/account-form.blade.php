@php($editing = $editing ?? false)
@php($account = $account ?? null)
<div class="account-form-grid">
    <div class="form-group">
        <label class="form-label" for="{{ $prefix }}-name">名前 <span class="required-label">必須</span></label>
        <input class="form-control" id="{{ $prefix }}-name" name="name" type="text" value="{{ $editing ? $account->name : old('name') }}" maxlength="100" required>
    </div>
    @if ($type === 'parent')
        <div class="form-group">
            <label class="form-label" for="{{ $prefix }}-email">メールアドレス <span class="required-label">必須</span></label>
            <input class="form-control" id="{{ $prefix }}-email" name="email" type="email" value="{{ $editing ? $account->email : old('email') }}" maxlength="255" required>
        </div>
    @endif
    <div class="form-group">
        <label class="form-label" for="{{ $prefix }}-login-id">ログインID <span class="required-label">必須</span></label>
        <input class="form-control" id="{{ $prefix }}-login-id" name="login_id" type="text" value="{{ $editing ? $account->login_id : old('login_id') }}" maxlength="100" required>
    </div>
    <div class="form-group">
        <label class="form-label" for="{{ $prefix }}-password">パスワード @unless($editing)<span class="required-label">必須</span>@endunless</label>
        <input class="form-control" id="{{ $prefix }}-password" name="password" type="password" minlength="8" autocomplete="new-password" @required(!$editing)>
        @if ($editing)<p class="form-help">変更しない場合は空欄のままにしてください。</p>@endif
    </div>
    <div class="form-group">
        <label class="form-label" for="{{ $prefix }}-password-confirmation">パスワード確認</label>
        <input class="form-control" id="{{ $prefix }}-password-confirmation" name="password_confirmation" type="password" minlength="8" autocomplete="new-password" @required(!$editing)>
    </div>
</div>
