@extends('layouts/app')

@section('title')
Travel | {{ config('app.name') }}
@endsection

@section('content')

    @component('layouts/title')
        Travel
    @endcomponent

<p>You are assigned to {{ name }}; however, you need to {{ action }} before we can book travel for you. Check the dashboard for further information.</p>

<a href="/">Go back to the dashboard</a>
@endsection
