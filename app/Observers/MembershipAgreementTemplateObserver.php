<?php

declare(strict_types=1);

namespace App\Observers;

use App\Jobs\PushToJedi;
use App\Models\MembershipAgreementTemplate;
use App\Models\Signature;

class MembershipAgreementTemplateObserver
{
    public function saved(MembershipAgreementTemplate $membershipAgreementTemplate): void
    {
        if (MembershipAgreementTemplate::count() < 2) {
            return;
        }

        $previousTemplate = MembershipAgreementTemplate::orderByDesc('updated_at')->first();

        $signatures = Signature::where('membership_agreement_template_id', '<', $previousTemplate->id)
            ->where('complete', true)
            ->with('user')
            ->get();

        foreach ($signatures as $signature) {
            PushToJedi::dispatch(
                $signature->user,
                MembershipAgreementTemplate::class,
                $membershipAgreementTemplate->id,
                'saved'
            );
        }
    }
}
