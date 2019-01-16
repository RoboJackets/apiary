<?php

namespace App\Nova\Tools;

use Laravel\Nova\Nova;
use Laravel\Nova\Tool;

class AttendanceReport extends Tool
{
    /**
     * Perform any tasks that need to happen when the tool is booted.
     *
     * @return void
     */
    public function boot()
    {
        // CSS and JS are included globally as they're mixed globally
    }

    /**
     * Build the view that renders the navigation links for the tool.
     *
     * @return \Illuminate\View\View
     */
    public function renderNavigation()
    {
        return view('nova/attendancereportnav');
    }
}
