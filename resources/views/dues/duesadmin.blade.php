@extends('layouts/app')

@section('title')
Dues Admin | {{ config('app.name') }}
@endsection

@section('content')
@component('layouts/title')
  Dues Admin
@endcomponent

<dues-admin-table>
</dues-admin-table>

@endsection
