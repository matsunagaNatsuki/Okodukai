@extends('layouts.app')

@section('title', '保護者プロフィール | おこづかい')

@section('content')
    <div class="page-heading">
        <p class="page-heading__eyebrow">PARENT PROFILE</p>
        <h1>プロフィール</h1>
        <p class="page-heading__description">名前とプロフィール画像を変更できます。</p>
    </div>

    <section class="profile-card">
        <div class="profile-current">
            <img
                class="profile-avatar"
                src="{{ $parent->profile_image ? asset('storage/'.$parent->profile_image) : asset('images/default-profile.svg') }}"
                alt="{{ $parent->name }}のプロフィール画像"
                data-profile-preview
            >
            <div>
                <h2>{{ $parent->name }}</h2>
                <p>{{ $parent->email }}</p>
                <span class="role-badge role-badge--parent">保護者</span>
            </div>
        </div>

        {{--- 保護者用プロフィールの更新処理--}}
        <form method="POST" action="{{ route('parent.profile.update') }}" enctype="multipart/form-data" data-loading data-loading-message="更新しています..." novalidate>
            @csrf
            @method('PUT')

            <div class="form-group">
                <label class="form-label" for="name">名前 <span class="required-label">必須</span></label>
                <input class="form-control
                @error('name') is-invalid @enderror" id="name" name="name" type="text" value="{{ old('name', $parent->name) }}" maxlength="100" autocomplete="name" required>
                @error('name')
                <p class="field-error">
                    {{ $message }}
                </p>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="profile_image">プロフィール画像</label>
                <input class="form-control file-control @error('profile_image') is-invalid @enderror" id="profile_image" name="profile_image" type="file" accept="image/jpeg,image/png,image/webp" data-profile-image-input>
                <p class="form-help">JPEG・PNG・WebP形式、2MB以下の画像を選択してください。</p>
                @error('profile_image')
                <p class="field-error">
                    {{ $message }}
                </p>
                @enderror
            </div>

            <div class="profile-email-readonly">
                <span>メールアドレス</span>
                <strong>{{ $parent->email }}</strong>
                <small>メールアドレスはこの画面では変更できません。</small>
            </div>

            <button class="button button--primary button--block" type="submit">プロフィールを更新する</button>
        </form>
    </section>
@endsection
