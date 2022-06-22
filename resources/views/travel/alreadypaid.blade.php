@extends('layouts/app')

@section('title')
Travel | {{ config('app.name') }}
@endsection

@section('content')
@component('layouts/title')
  Travel
@endcomponent

<p>
You do not need to make any payment at this time.
</p>

<a href="/">Go back to the dashboard</a>

@endsection
