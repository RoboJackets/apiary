@extends('layouts/app')

@section('title')
Remote Attendance | {{ config('app.name') }}
@endsection

@section('content')
@component('layouts/title')
  Remote Attendance
@endcomponent

<p>
{{ $message }}
</p>

<a href="/">Go back to the dashboard</a>

@endsection
