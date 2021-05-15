@extends('layouts/app')

@section('title')
Travel | {{ config('app.name') }}
@endsection

@section('content')

    @component('layouts/title')
        Travel
    @endcomponent

<p>You don't have any upcoming travel assigned. Talk to your project manager to learn more.</p>

<a href="/">Go back to the dashboard</a>
@endsection
