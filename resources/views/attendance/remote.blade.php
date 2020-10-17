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
@isset($linkDestination)
 <a href="$linkDestination">{{ $linkDestination }}</a>
@endif
</p>

<a href="/">Go back to the dashboard</a>

@endsection
