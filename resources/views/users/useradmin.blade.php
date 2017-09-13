@extends('layouts/app')

@section('title')
Users Admin | {{ env('APP_NAME') }}
@endsection

@section('content')
@component('layouts/title')
  Users Admin
@endcomponent

<users-admin-table>
</users-admin-table>

@endsection
