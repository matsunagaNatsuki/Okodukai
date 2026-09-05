@extends('layouts.app')

@section('title', 'おこづかい')

@section('content')
<div class="page-heading">
    <p class="page-heading__eyebrow">OKOZUKAI</p>
    <h1><i class="fa-solid fa-child-reaching"></i>お子様管理一覧</h1>
    <div class="page-profile">
        <img class="child-card__avatar" src="{{ $child->profile_image ? asset('storage/'.$child->profile_image) : asset('images/default-profile.svg') }}" alt="{{ $child->name }}のプロフィール画像">
        <p>
            <span class="child-name">{{ $child->name }}</span>さんのお子様管理データです
        </p>
        {{--<p>{{ $description }}</p>--}}
    </div>
</div>


<a class="child-data" href="{{ route('parent.pocket-money.show', $child) }}">
    <section class="page-card page-card--centered">
        <i class="fa-solid fa-wallet" style="padding-top: 10px;"></i>おこづかい入金
    </section>
</a>

<a class="child-data" href="{{ route('parent.chores.performance', $child) }}">
    <section class="page-card page-card--centered">
        <i class="fa-solid fa-file-pen" style="padding-top: 10px;"></i>お手伝いの記録
    </section>
</a>

<a class="child-data" href="{{ route('parent.chores.history', $child) }}">
    <section class="page-card page-card--centered">
        <i class="fa-solid fa-broom" style="padding-top: 10px;"></i>お手伝いの実績
    </section>
</a>

<a class="child-data" href="{{ route('parent.child-payment.history', $child) }}">
    <section class="page-card page-card--centered">
        <i class="fa-solid fa-cart-shopping" style="padding-top: 10px;"></i>使用したお金
    </section>
</a>

<a class="child-data" href="{{ route('parent.savings.show', $child) }}">
    <section class="page-card page-card--centered">
        <i class="fa-solid fa-sack-dollar" style="padding-top: 10px;"></i>貯金の目標
    </section>
</a>
@endsection