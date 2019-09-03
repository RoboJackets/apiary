<?php

namespace Robojackets\Apiary\Nova\CollectAttendance;

use Laravel\Nova\ResourceTool;

class CollectAttendance extends ResourceTool
{
    /**
     * Get the displayable name of the resource tool.
     *
     * @return string
     */
    public function name()
    {
        return 'Collect Attendance';
    }

    /**
     * Get the component name for the resource tool.
     *
     * @return string
     */
    public function component()
    {
        return 'collect-attendance';
    }
}
