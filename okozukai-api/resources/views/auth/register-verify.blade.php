<h1>確認コードを入力してください</h1>

<p>{{ $email }} に4桁の確認コードを送信しました。</p>

<form method="POST" action="{{ route('parent.register.verify.store', ['token' => $token]) }}" novalidate>
    @csrf

    <div>
        <label for="code">確認コード</label>

        <input id="code" type="text" name="code" value="{{ old('code') }}" maxlength="4" inputmode="numeric" autocomplete="one-time-code" required autofocus>

        @error('code')
        <div>{{ $message }}</div>
        @enderror
    </div>

    <button type="submit">確認する</button>
</form>