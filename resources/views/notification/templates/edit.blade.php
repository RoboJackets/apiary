@extends('layouts/app')

@section('title')
    Notification Templates | {{ config('app.name') }}
@endsection

@section('content')
    @component('layouts/title')
        Edit Notification Template
    @endcomponent

    <notification-templates-edit-form template-id="{{$id}}"></notification-templates-edit-form>

@endsection