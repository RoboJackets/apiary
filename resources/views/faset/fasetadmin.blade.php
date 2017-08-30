@extends('layouts/app')

@section('title')
FASET Admin | {{ env('APP_NAME') }}
@endsection

@section('content')
@component('layouts/title')
  FASET Admin
@endcomponent

<faset-admin-table>
</faset-admin-table>

@endsection
