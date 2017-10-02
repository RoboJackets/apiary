@extends('layouts/app')

@section('title')
Dues | {{ config('app.name') }}
@endsection

@section('content')
@component('layouts/title')
  Pay Dues
@endcomponent

<dues-sequence user-uid="{{auth()->user()->uid}}"></dues-sequence>

@endsection
