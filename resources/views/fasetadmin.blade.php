@extends('layouts/app')

@section('title')
FASET Admin | {{ env('APP_NAME') }}
@endsection

@section('content')

<h1> FASET Admin </h1>

<datatable 
  data-url="/api/v1/faset"
  :columns="['id','faset_name', 'faset_email']">
          
</datatable>


@endsection
