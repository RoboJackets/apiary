@extends('layouts/app')

@section('title')
Payment Problem | {{ config('app.name') }}
@endsection

@section('content')
@component('layouts/title')
  Payment Problem
@endcomponent

<p>
{{ $message }}
</p>

<p>
If you believe this is a system error, post in <a href="slack://open?team=T033JPZLT&id=C29Q3D8K0">#it-helpdesk</a> for assistance.
</p>

<a href="/">Go back to the dashboard</a>

@endsection
