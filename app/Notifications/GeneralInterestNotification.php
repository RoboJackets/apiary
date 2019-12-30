<?php

declare(strict_types=1);

// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter,SlevomatCodingStandard.Functions.UnusedParameter,SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint,SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint

namespace App\Notifications;

use App\Mail\GeneralInterestInvite as Mailable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class GeneralInterestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     *
     * @return array<string>
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return Mailable
     */
    public function toMail($notifiable): Mailable
    {
        // Get data to pass to the mailable
        $email = $notifiable->routeNotificationForMail();
        $token = $notifiable->getVisitToken();

        if ($notifiable instanceof \App\RecruitingCampaignRecipient) {
            // Update the notifiable to show it has been sent
            $notifiable->notified_at = date('Y-m-d H:i:s', time());
            $notifiable->save();
        }

        return (new Mailable($token))->to($email);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return array<string,string>
     */
    public function toArray($notifiable): array
    {
        return [];
    }
}
