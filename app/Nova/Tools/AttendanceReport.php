<?php

declare(strict_types=1);

namespace App\Nova\Tools;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Laravel\Nova\Menu\MenuSection;
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
     *
     * @phan-suppress PhanTypeMismatchReturn
     */
    public function renderNavigation(): View
    {
        return view('nova/attendancereportnav');
    }

    /**
     * Build the menu that renders the navigation links for the tool.
     */
    public function menu(Request $request): MenuSection
    {
        return MenuSection::make('Attendance Report')
            ->path('/attendance-report')
            ->icon('identification');
    }
}
