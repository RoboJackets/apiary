<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.ControlStructures.RequireTernaryOperator.TernaryOperatorNotUsed

namespace App\Mail\Dues;

use App\Models\DuesPackage;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PackageExpirationReminder extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $packageCount = DuesPackage::whereDate('access_end', '>', Carbon::now())
            ->whereDate('access_end', '<', Carbon::now()->addDays(7))
            ->count();

        $users = User::permission('update-dues-packages')
            ->get()
            ->map(static fn (User $user): Address => new Address($user->gt_email, $user->full_name))
            ->toArray();

        if ($packageCount === 1) {
            $package = DuesPackage::whereDate('access_end', '>', Carbon::now())
                ->whereDate('access_end', '<', Carbon::now()->addDays(7))
                ->sole();

            $subject = $package->name.' dues expire on '.$package->access_end->format('l, F j');
        } else {
            $packages = DuesPackage::whereDate('access_end', '>', Carbon::now())
                ->whereDate('access_end', '<', Carbon::now()->addDays(7))
                ->get();

            $expirations = $packages
                ->map(static fn (DuesPackage $package): string => $package->access_end->format('l, F j'))
                ->unique();

            if ($expirations->count() === 1) {
                $subject = 'Upcoming dues deadline on '.$expirations->first();
            } else {
                $subject = 'Upcoming dues deadlines within the next 7 days';
            }
        }

        return new Envelope(
            from: new Address('noreply@my.robojackets.org', 'RoboJackets'),
            to: $users,
            replyTo: [new Address('support@robojackets.org', 'RoboJackets')],
            subject: $subject,
            tags: ['package-expiration-reminder']
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $packages = DuesPackage::whereDate('access_end', '>', Carbon::now())
            ->whereDate('access_end', '<', Carbon::now()->addDays(7))
            ->get();

        $userCount = User::inactive()->accessActive()->count();

        return new Content(
            text: 'mail.dues.packageexpirationreminder',
            with: [
                'packages' => $packages,
                'userCount' => $userCount,
            ]
        );
    }
}
