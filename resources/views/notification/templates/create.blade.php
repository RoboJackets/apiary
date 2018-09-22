@extends('layouts/app')

@section('title')
    Notification Templates | {{ config('app.name') }}
@endsection

@section('content')
    @component('layouts/title')
        Create Notification Template
    @endcomponent

    <notification-templates-create-form></notification-templates-create-form>

@endsection
