@extends('layouts/app')

@section('title')
Event Admin | {{ env('APP_NAME') }}
@endsection

@section('content')
@component('layouts/title')
  Event Admin
@endcomponent

<events-admin-table>
</events-admin-table>

@endsection
