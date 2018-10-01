@extends('layouts/app')

@section('title')
Dues | {{ config('app.name') }}
@endsection

@section('content')
@component('layouts/title')
  Pay Dues
@endcomponent

<dues-sequence user-uid="{{auth()->user()->uid}}" :user-teams="{{auth()->user()->teams}}"></dues-sequence>

@endsection
