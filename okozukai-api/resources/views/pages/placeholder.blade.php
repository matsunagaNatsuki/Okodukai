@extends('layouts.app')

@section('title', 'おこづかい')

@section('content')
<div class="page-heading">
    <p class="page-heading__eyebrow">OKOZUKAI</p>
    <h1>お子様管理</h1>
    <p>{{ $description }}</p>
</div>

<section class="page-card page-card--centered">
    <a class="child-data" href="{{ route('parent.pocket-money.show', $child) }}">
        おこづかい入金
    </a>
</section>
<section class="page-card page-card--centered">
    <a class="child-data" href="{{ route('parent.chores.performance', $child) }}">
        お手伝い実績登録
    </a>
</section>
<section class="page-card page-card--centered">
    <a class="child-data" href="{{ route('parent.chores.history', $child) }}">
        お手伝い履歴
    </a>
</section>
<section class="page-card page-card--centered">
    <a class="child-data" href="{{ route('parent.child-payment.history', $child) }}">
        支出履歴
    </a>
</section>
<section class="page-card page-card--centered">
    <a class="child-data" href="{{ route('parent.savings.show', $child) }}">
        貯金目標
    </a>
</section>
@endsection