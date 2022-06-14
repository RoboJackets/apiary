@extends('layouts/app')

@section('title')
Travel | {{ config('app.name') }}
@endsection

@section('content')
@component('layouts/title')
  Travel
@endcomponent

<p>
You do not need to submit any documents at this time.
</p>

<a href="/">Go back to the dashboard</a>

@endsection
