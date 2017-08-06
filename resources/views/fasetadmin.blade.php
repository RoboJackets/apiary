@extends('layouts/app')

@section('title')
FASET Admin | {{ env('APP_NAME') }}
@endsection

@section('content')
<div class="container">
  <div class="row">
    <div class="col-12">
      <h1 class="my-4"> FASET Admin </h1>
    </div>
  </div>
</div>
<datatable 
  data-url="/api/v1/faset"
  :columns="[{'title': 'ID', 'data':'id'}, {'title': 'Timestamp', 'data':'created_at'}, {'title': 'Name', 'data':'faset_name'}, {'title': 'Email', 'data':'faset_email'}]">
          
</datatable>


@endsection
