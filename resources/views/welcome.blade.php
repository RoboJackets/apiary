@extends('layouts/app')

@section('title')
{{ config('app.name') }}
@endsection

@section('content')

@component('layouts/title')
  Dashboard
@endcomponent

<div class="row">
  @if(true)
  <div class="col-sm-6 col-md-3 col-lg-4">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title">
          Pay Dues
        </h4>
        <p class="card-text">
          To continue your membership in RoboJackets, we need you to update your information and pay dues.
        </p>
        <a href="/dues">Pay Dues Now</a>
      </div>
    </div>
  </div>
  @endif
</div>


@endsection