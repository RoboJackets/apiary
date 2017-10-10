@extends('layouts/app')

@section('title')
Dues Transaction | {{ config('app.name') }}
@endsection

@section('content')
@component('layouts/title')
  Dues Transaction
@endcomponent

<dues-transaction
    dues-transaction-id="{{$id}}">
    
</dues-transaction>

@endsection
