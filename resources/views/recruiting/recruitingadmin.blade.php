@extends('layouts/app')

@section('title')
Recruiting Admin | {{ config('app.name') }}
@endsection

@section('content')
@component('layouts/title')
  Recruiting Admin
@endcomponent

<recruiting-admin-table>
</recruiting-admin-table>

@endsection
