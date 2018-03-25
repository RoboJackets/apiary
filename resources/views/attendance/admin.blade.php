@extends('layouts/app')

@section('title')
    Attendance Export | {{ config('app.name') }}
@endsection

@section('content')
    @component('layouts/title')
        Attendance Admin
    @endcomponent

    <ul class="nav nav-tabs">
        <li class="nav-item">
            <a class="nav-link active" id="edit-tab" data-toggle="tab" href="#update">Update</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="export-tab" data-toggle="tab" href="#export">Export</a>
        </li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane show active" id="update">
            <h3>Add</h3>
            <em>Note:</em> This should be used for adding past attendance only. Normal team attendance should be recorded through the kiosk.<br/>
            <attendance-manual-add></attendance-manual-add>
            <h3>Delete</h3>
            <div class="row">
                <div class="col-12">
                    Need to delete an attendance record? Ask in #it-helpdesk.
                </div>
            </div>
        </div>

        <div class="tab-pane" id="export">
            <h3>Export</h3>
            <attendance-export></attendance-export>
        </div>
    </div>

@endsection
