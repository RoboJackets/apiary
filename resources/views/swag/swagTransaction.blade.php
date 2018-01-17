@extends('layouts/app')

@section('title')
    Swag Distribution | {{ config('app.name') }}
@endsection

@section('content')
    @component('layouts/title')
        Swag Distribution
    @endcomponent

    <swag-transaction
            dues-transaction-id="{{$id}}">

    </swag-transaction>

@endsection
