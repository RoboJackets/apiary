@extends('layouts/app')

@section('title')
Event Admin | {{ config('app.name') }}
@endsection

@section('content')
@component('layouts/title')
  Event Admin
@endcomponent
<div class="row">
    <div class="col-sm-12 col-md-3">
        <a href="{{ route('events.create') }}" class="btn btn-primary btn-above-table" role="button">New Event</a>
    </div>
</div>
<events-admin-table></events-admin-table>

@endsection
