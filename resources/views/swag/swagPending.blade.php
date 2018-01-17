@extends('layouts/app')

@section('title')
    Pending Swag Distribution | {{ config('app.name') }}
@endsection

@section('content')
    @component('layouts/title')
        Pending Swag Distribution
    @endcomponent

    <swag-pending-table>
    </swag-pending-table>

@endsection
