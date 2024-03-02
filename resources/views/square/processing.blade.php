@extends('layouts/app')

@section('title')
Payment Processing | {{ config('app.name') }}
@endsection

@section('content')
@component('layouts/title')
  Payment Processing
@endcomponent

<p>
Your payment is being processed, and should finalize in the next few minutes. If you do not receive a confirmation email from {{ config('app.name') }} soon, post in <a href="slack://open?team=T033JPZLT&id=C29Q3D8K0">#it-helpdesk</a> for assistance.
</p>

<a href="/">Go back to the dashboard</a>

@endsection
