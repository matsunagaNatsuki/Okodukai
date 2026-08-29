@extends('layouts.app')

@section('title', '家族アカウント管理 | おこづかい')

@section('content')
<div class="page-heading">
    <p class="page-heading__eyebrow">FAMILY ACCOUNTS</p>
    <h1><i class="fa-solid fa-people-roof"></i>家族アカウント</h1>
    <p class="page-heading__description">家族コード：<strong>{{ $family->family_code }}</strong></p>
</div>

{{-- 家族アカウントの追加処理 --}}
<section class="account-create-section">
    <details class="account-create-panel" @if ($errors->any()) open @endif>
        <summary><i class="fa-solid fa-person"></i>保護者を追加</summary>
        <form method="POST" action="{{ route('parent.family-account.parents.store') }}" data-loading data-loading-message="追加しています..." novalidate>
            @csrf
            {{-- 保護者追加用の入力フォームとして、HTML要素のIDに「new-parent」を付けた共通フォームを表示 --}}
            @include('parent.family-account.partials.account-form', ['type' => 'parent', 'prefix' => 'new-parent'])
            <button class="button button--primary" type="submit">保護者を追加する</button>
        </form>
    </details>

    <details class="account-create-panel">
        <summary><i class="fa-solid fa-child-dress"></i>子どもを追加</summary>
        <form method="POST" action="{{ route('parent.family-account.children.store') }}" data-loading data-loading-message="追加しています..." novalidate>
            @csrf
            {{-- お子様追加用の入力フォームとして、HTML要素のIDに「new-child」を付けた共通フォームを表示 --}}
            @include('parent.family-account.partials.account-form', ['type' => 'child', 'prefix' => 'new-child'])
            <button class="button button--primary" type="submit">子どもを追加する</button>
        </form>
    </details>
</section>

<div class="ajax-message" data-ajax-message aria-live="polite"></div>

<div class="family-account-list">
    {{-- 家族アカウント全員の表示 --}}
    @foreach ($accounts as $account)
    {{-- ログイン中の本人または家族のオーナーは削除不可 --}}
    @php($cannotDelete = auth()->id() === $account->id || $family->owner_user_id === $account->id)
    <article class="family-account-card" data-family-account-card="{{ $account->id }}">
        <div class="family-account-summary">
            <img class="child-card__avatar" src="{{ $account->profile_image ? asset('storage/'.$account->profile_image) : asset('images/default-profile.svg') }}" alt="{{ $account->name }}のプロフィール画像">
            <div class="family-account-info">
                <div class="family-account-name-row">
                    <h2>{{ $account->name }}</h2>
                    <span class="role-badge role-badge--{{ $account->role }}">{{ $account->role === 'parent' ? '保護者' : 'お子様' }}</span>
                    @if ($family->owner_user_id === $account->id)<span class="owner-badge">家族代表</span>@endif
                    @if (auth()->id() === $account->id)<span class="self-badge">ログイン中</span>@endif
                </div>
                @if ($account->role === 'parent')
                <p>メールアドレス：{{ $account->email }}</p>
                @else
                <p>ログインID：{{ $account->login_id }}</p>
                @endif
            </div>
        </div>

        {{-- 家族アカウントの編集処理--}}
        <details class="history-editor family-account-editor">
            <summary>編集する</summary>
            <form method="POST" action="{{ route('parent.family-account.update', $account) }}" data-loading data-loading-message="更新しています..." novalidate>
                @csrf
                @method('PUT')
                {{-- 家族のアカウント情報を別のbladeで表示 --}}
                @include('parent.family-account.partials.account-form', ['type' => $account->role, 'prefix' => 'account-'.$account->id, 'account' => $account, 'editing' => true])
                <div class="card-actions">
                    <button class="button button--primary button--small" type="submit">更新する</button>
                </div>
            </form>
        </details>

        {{-- ログイン中の本人または家族代表は削除不可 --}}
        <div class="family-account-delete">
            @if ($cannotDelete)
            <p class="protected-account-note">
                {{ auth()->id() === $account->id ? 'ログイン中のアカウントは削除できません。' : '家族代表のアカウントは削除できません。' }}
            </p>
            {{-- ログイン中の本人または家族代表でなければ削除可能--}}
            @else
            <button class="button button--danger-subtle button--small" type="button" data-ajax-delete data-delete-url="{{ route('parent.family-account.destroy', $account) }}" data-delete-target="[data-family-account-card]" data-delete-title="家族アカウントの削除" data-delete-message="「{{ $account->name }}」のアカウントを削除しますか？この操作は元に戻せません。">削除する</button>
            @endif
        </div>
    </article>
    @endforeach
</div>
@endsection