@extends('layouts.app')

@section('title', 'おこづかい')

@section('content')
<div class="page-heading">
    <p class="page-heading__eyebrow">OKOZUKAI</p>
    <h1><i class="fa-solid fa-child-reaching"></i>お子様管理一覧</h1>
    {{--<p>{{ $description }}</p>--}}
    <p>
        <span class="child-name">{{ $child->name }}</span>さんのお子様管理データです
    </p>
</div>

<section class="page-card page-card--centered">
    <a class="child-data" href="{{ route('parent.pocket-money.show', $child) }}">
        <i class="fa-solid fa-wallet"></i>おこづかい入金
    </a>
</section>
<section class="page-card page-card--centered">
    <a class="child-data" href="{{ route('parent.chores.performance', $child) }}">
        <i class="fa-solid fa-file-pen"></i>お手伝いの記録
    </a>
</section>
<section class="page-card page-card--centered">
    <a class="child-data" href="{{ route('parent.chores.history', $child) }}">
        <i class="fa-solid fa-broom"></i>お手伝いの実績
    </a>
</section>
<section class="page-card page-card--centered">
    <a class="child-data" href="{{ route('parent.child-payment.history', $child) }}">
        <i class="fa-solid fa-cart-shopping"></i>使用したお金
    </a>
</section>
<section class="page-card page-card--centered">
    <a class="child-data" href="{{ route('parent.savings.show', $child) }}">
        <i class="fa-solid fa-sack-dollar"></i>貯金の目標
    </a>
</section>
@endsection