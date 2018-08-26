@extends('layouts/app')

@section('title')
FASET Admin | {{ config('app.name') }}
@endsection

@section('content')
@component('layouts/title')
  FASET Admin
@endcomponent

<faset-admin-table>
</faset-admin-table>

@endsection
