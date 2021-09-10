<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Signature;
use App\Notifications\MembershipAgreementSigned;

class SignatureObserver
{
    public function updated(Signature $signature): void
    {
        if (! $signature->complete) {
            return;
        }

        $signature->user->notify(new MembershipAgreementSigned($signature));
    }
}
