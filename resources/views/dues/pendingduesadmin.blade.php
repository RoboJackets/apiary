@extends('layouts/app')

@section('title')
Pending Dues Transactions | {{ config('app.name') }}
@endsection

@section('content')
@component('layouts/title')
  Pending Dues Transactions
@endcomponent

<pending-dues-table>
</pending-dues-table>

@endsection
