<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\AccessCard;
use App\Models\Attendance;

class AccessCardObserver
{
    public function saved(AccessCard $card): void
    {
        Attendance::where('access_card_number', '=', $card->access_card_number)
            ->update([
                'gtid' => $card->user->gtid,
            ]);
    }
}
