@extends('layouts/app')

@section('title')
Users Admin | {{ config('app.name') }}
@endsection

@section('content')
@component('layouts/title')
  Users Admin
@endcomponent

<users-admin-table>
</users-admin-table>

@endsection
