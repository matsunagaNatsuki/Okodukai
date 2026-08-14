@extends('layouts.app')

@section('title', $title . ' | おこづかい')

@section('content')
    <section class="page-card">
        <div class="page-heading">
            <p class="page-heading__eyebrow">OKOZUKAI</p>
            <h1>{{ $title }}</h1>
        </div>
        <p>{{ $description }}</p>
    </section>
@endsection
