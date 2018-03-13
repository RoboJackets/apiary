@extends('layouts/app')

@section('title')
    Attendance Export | {{ config('app.name') }}
@endsection

@section('content')
    @component('layouts/title')
        Attendance Export
    @endcomponent

    <attendance-export></attendance-export>

@endsection
