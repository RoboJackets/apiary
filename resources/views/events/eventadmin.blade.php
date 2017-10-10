@extends('layouts/app')

@section('title')
Event Admin | {{ config('app.name') }}
@endsection

@section('content')
@component('layouts/title')
  Event Admin
@endcomponent

<events-admin-table>
</events-admin-table>

@endsection
