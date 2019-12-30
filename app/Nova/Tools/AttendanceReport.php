<?php

declare(strict_types=1);

namespace App\Nova\Tools;

use Illuminate\View\View;
use Laravel\Nova\Tool;

class AttendanceReport extends Tool
{
    /**
     * Perform any tasks that need to happen when the tool is booted.
     */
    public function boot(): void
    {
        // CSS and JS are included globally as they're mixed globally
    }

    /**
     * Build the view that renders the navigation links for the tool.
     */
    public function renderNavigation(): View
    {
        return view('nova/attendancereportnav');
    }
}
