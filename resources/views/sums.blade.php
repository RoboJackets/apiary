@extends('layouts/app')

@section('title')
SUMS Access Request | {{ config('app.name') }}
@endsection

@section('content')
@component('layouts/title')
  SUMS Access Request
@endcomponent

<p>
{{ $message }}
</p>

<a href="/">Go back to the dashboard</a>

@endsection
