@extends('layouts/app')

@section('title')
Attendance Export | {{ config('app.name') }}
@endsection

@section('content')
@component('layouts/title')
  Attendance Export
@endcomponent

<p>
{{ $message }}
</p>

@endsection
