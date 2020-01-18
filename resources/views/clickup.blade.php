@extends('layouts/app')

@section('title')
ClickUp Access Request | {{ config('app.name') }}
@endsection

@section('content')
@component('layouts/title')
  ClickUp Access Request
@endcomponent

<p>
{{ $message }}
</p>

<a href="/">Go back to the dashboard</a>

@endsection
