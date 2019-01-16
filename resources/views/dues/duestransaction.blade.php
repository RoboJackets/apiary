@extends('layouts/app')

@section('title')
Dues Transaction | {{ config('app.name') }}
@endsection

@section('content')
@component('layouts/title')
  Dues Transaction
@endcomponent

{{-- The inner regex filters the permissions list to create-payment-* but not create-payment-own.
The outer regex takes the permissions and removes the create-payment- prefix. --}}
<dues-transaction
    dues-transaction-id="{{$id}}"
    payment-methods="{{join(',', preg_replace("/^create-payments-(.*)$/", "$1",
                        preg_grep("/^create-payments-.*(?<!own)$/", $perms)))}}">

</dues-transaction>

@endsection
