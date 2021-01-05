@extends('layouts/app')

@section('title')
Membership Agreement | {{ config('app.name') }}
@endsection

@section('content')
@component('layouts/title')
  Membership Agreement
@endcomponent

<p>
We already have a signed agreement on file for you for the most recent revision of the membership agreement. You can now pay dues if you haven't already.
</p>

<a href="/">Go back to the dashboard</a>

@endsection
