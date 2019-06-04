<?php

declare(strict_types=1);

namespace App\Nova\Tools;

use Laravel\Nova\Tool;
use Illuminate\View\View;

class AttendanceReport extends Tool
{
    /**
     * Perform any tasks that need to happen when the tool is booted.
     *
     * @return void
     */
    public function boot(): void
    {
        // CSS and JS are included globally as they're mixed globally
    }

    /**
     * Build the view that renders the navigation links for the tool.
     *
     * @return \Illuminate\View\View
     */
    public function renderNavigation(): View
    {
        return view('nova/attendancereportnav');
    }
}
