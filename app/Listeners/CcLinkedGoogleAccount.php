<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Models\User;
use Illuminate\Mail\Events\MessageSending;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

/**
 * Carbon-copies a user's linked Google account when a message is addressed only to that user.
 *
 * @phan-suppress PhanUnreferencedClass
 */
class CcLinkedGoogleAccount
{
    /**
     * Handle the event.
     */
    public function handle(MessageSending $event): void
    {
        $message = $event->message;

        $to = array_values($message->getTo());

        if (count($to) !== 1) {
            return;
        }

        $users = User::query()
            ->whereRaw('lower(gt_email) = ?', [strtolower($to[0]->getAddress())])
            ->get();

        if ($users->count() !== 1) {
            return;
        }

        $user = $users->sole();

        $gmailAddress = $user->gmail_address;

        if (! is_string($gmailAddress)) {
            return;
        }

        $gmailAddress = trim($gmailAddress);

        if ($gmailAddress === '') {
            return;
        }

        if ($this->messageIncludesAddress($message, $gmailAddress)) {
            return;
        }

        $message->addCc(new Address($gmailAddress, $user->name));
    }

    private function messageIncludesAddress(Email $message, string $address): bool
    {
        $normalizedAddress = strtolower($address);

        $recipients = [
            ...$message->getTo(),
            ...$message->getCc(),
            ...$message->getBcc(),
        ];

        foreach ($recipients as $recipient) {
            if (strtolower($recipient->getAddress()) === $normalizedAddress) {
                return true;
            }
        }

        return false;
    }
}
