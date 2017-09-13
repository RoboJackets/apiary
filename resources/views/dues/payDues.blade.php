@extends('layouts/app')

@section('title')
Dues | {{ env('APP_NAME') }}
@endsection

@section('content')
@component('layouts/title')
  Pay Dues
@endcomponent

<dues-required-info user-uid="{{auth()->user()->uid}}"></dues-required-info>

@endsection
