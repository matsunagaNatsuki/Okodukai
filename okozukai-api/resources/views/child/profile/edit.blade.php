@extends('layouts.app')

@section('title', '子どもプロフィール | おこづかい')

@section('content')
<div class="page-heading page-heading--with-action">
    <div>
        <p class="page-heading__eyebrow">CHILD PROFILE</p>
        <h1><i class="fa-solid fa-child-dress"></i>プロフィール</h1>
        <p class="page-heading__description">お名前とプロフィールの画像を変更できます。</p>
    </div>
    <a class="button button--secondary" href="{{ route('child.home') }}">ホームへ戻る</a>
</div>

<section class="profile-card">
    <div class="profile-current">
        <img
            class="profile-avatar"
            src="{{ $child->profile_image ? asset('storage/'.$child->profile_image) : asset('images/default-profile.svg') }}"
            alt="{{ $child->name }}のプロフィール画像"
            data-profile-preview>
        <div>
            <h2>{{ $child->name }}</h2>
            <span class="role-badge role-badge--child">子ども</span>
        </div>
    </div>

    <form method="POST" action="{{ route('child.profile.update') }}" enctype="multipart/form-data" data-loading data-loading-message="新しく変更しています..." novalidate>
        @csrf
        @method('PUT')

        <div class="form-group">
            <label class="form-label" for="name">お名前 <span class="required-label">必ず入力してね</span></label>
            <input class="form-control @error('name') is-invalid @enderror" id="name" name="name" type="text" value="{{ old('name', $child->name) }}" maxlength="100" autocomplete="name" required>
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

        <button class="button button--primary button--block" type="submit">プロフィールを変更する</button>
    </form>
</section>
@endsection