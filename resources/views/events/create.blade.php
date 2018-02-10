@extends('layouts/app')

@section('title')
    Event Admin | {{ config('app.name') }}
@endsection

@section('content')
    @component('layouts/title')
        Create Event
    @endcomponent

    <event-create-form></event-create-form>

@endsection
