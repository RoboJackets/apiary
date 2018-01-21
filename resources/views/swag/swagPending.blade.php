@extends('layouts/app')

@section('title')
    Pending Swag Distribution | {{ config('app.name') }}
@endsection

@section('content')
    @component('layouts/title')
        Pending Swag Distribution
    @endcomponent

    <swag-table table-filter="pending">
    </swag-table>

@endsection
