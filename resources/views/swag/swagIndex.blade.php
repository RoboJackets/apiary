@extends('layouts/app')

@section('title')
    Swag Distribution | {{ config('app.name') }}
@endsection

@section('content')
    @component('layouts/title')
        Swag Distribution
    @endcomponent

    <swag-table table-filter="index">
    </swag-table>

@endsection
