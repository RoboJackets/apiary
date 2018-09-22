@extends('layouts/app')

@section('title')
    Notification Templates | {{ config('app.name') }}
@endsection

@section('content')
    @component('layouts/title')
        Notification Templates
    @endcomponent
    <div class="row">
        <div class="col-sm-12 col-md-3">
            <a href="{{ route('admin.notification.templates.create') }}"
               class="btn btn-primary btn-above-table" role="button">New Template</a>
        </div>
    </div>
    <notification-templates-admin-table>
    </notification-templates-admin-table>

@endsection
