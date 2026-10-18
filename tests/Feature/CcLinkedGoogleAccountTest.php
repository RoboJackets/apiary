<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Listeners\CcLinkedGoogleAccount;
use App\Mail\Dues\TransactionReminder;
use App\Models\DuesPackage;
use App\Models\User;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Transport\ArrayTransport;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Tests\TestCase;

final class CcLinkedGoogleAccountTest extends TestCase
{
    public function test_individual_message_ccs_linked_google_account(): void
    {
        $user = $this->userWithGoogleAccount('gburdell3@gatech.edu', 'george.burdell@gmail.com');

        $message = $this->messageAddressedTo($user->gt_email, $user->name);

        $this->apply($message);

        $this->assertSame(['george.burdell@gmail.com'], $this->ccAddresses($message));
        $this->assertSame($user->name, $message->getCc()[0]->getName());
    }

    public function test_sent_dues_transaction_reminder_ccs_linked_google_account(): void
    {
        $user = User::factory()->create([
            'first_name' => 'George',
            'preferred_name' => null,
            'last_name' => 'Burdell',
        ]);
        $user->primary_affiliation = 'student';
        $user->gmail_address = 'george.burdell@gmail.com';
        $user->save();

        $package = DuesPackage::factory()->create();
        $package->available_for_purchase = true;
        $package->effective_end = now()->addHour();
        $package->restricted_to_students = true;
        $package->save();

        $transport = Mail::mailer('array')->getSymfonyTransport();
        $this->assertInstanceOf(ArrayTransport::class, $transport);
        $transport->flush();

        Mail::mailer('array')->sendNow(new TransactionReminder($user));

        $sentMessage = $transport->messages()->sole()->getOriginalMessage();
        $this->assertInstanceOf(Email::class, $sentMessage);
        $this->assertSame([$user->gt_email], $this->addresses($sentMessage->getTo()));
        $this->assertSame(['george.burdell@gmail.com'], $this->ccAddresses($sentMessage));
        $this->assertSame($user->name, $sentMessage->getCc()[0]->getName());
    }

    public function test_message_without_linked_google_account_is_not_copied(): void
    {
        $user = User::factory()->create();

        $message = $this->messageAddressedTo($user->gt_email);

        $this->apply($message);

        $this->assertSame([], $message->getCc());
    }

    public function test_google_account_matching_recipient_is_not_copied_again(): void
    {
        $this->userWithGoogleAccount('gburdell3@gatech.edu', 'GBurdell3@gatech.edu');

        $message = $this->messageAddressedTo('gburdell3@gatech.edu');

        $this->apply($message);

        $this->assertSame([], $message->getCc());
    }

    public function test_recipient_address_match_is_case_insensitive(): void
    {
        $this->userWithGoogleAccount('GBurdell3@gatech.edu', 'george.burdell@gmail.com');

        $message = $this->messageAddressedTo('gburdell3@gatech.edu');

        $this->apply($message);

        $this->assertSame(['george.burdell@gmail.com'], $this->ccAddresses($message));
    }

    public function test_linked_google_account_is_trimmed(): void
    {
        $user = $this->userWithGoogleAccount('gburdell3@gatech.edu', '  george.burdell@gmail.com  ');

        $message = $this->messageAddressedTo($user->gt_email);

        $this->apply($message);

        $this->assertSame(['george.burdell@gmail.com'], $this->ccAddresses($message));
    }

    public function test_blank_linked_google_account_is_not_copied(): void
    {
        $user = $this->userWithGoogleAccount('gburdell3@gatech.edu', '   ');

        $message = $this->messageAddressedTo($user->gt_email);

        $this->apply($message);

        $this->assertSame([], $message->getCc());
    }

    public function test_existing_carbon_copy_is_kept_when_google_account_is_added(): void
    {
        $user = $this->userWithGoogleAccount('gburdell3@gatech.edu', 'george.burdell@gmail.com');

        $message = $this->messageAddressedTo($user->gt_email);
        $message->addCc(new Address('treasurer@robojackets.org', 'Treasurer'));

        $this->apply($message);

        $this->assertSame(
            ['treasurer@robojackets.org', 'george.burdell@gmail.com'],
            $this->ccAddresses($message)
        );
    }

    public function test_google_account_already_copied_is_not_duplicated(): void
    {
        $user = $this->userWithGoogleAccount('gburdell3@gatech.edu', 'george.burdell@gmail.com');

        $message = $this->messageAddressedTo($user->gt_email);
        $message->addCc('George.Burdell@gmail.com');

        $this->apply($message);

        $this->assertSame(['George.Burdell@gmail.com'], $this->ccAddresses($message));
    }

    public function test_google_account_already_blind_copied_is_not_copied_again(): void
    {
        $user = $this->userWithGoogleAccount('gburdell3@gatech.edu', 'george.burdell@gmail.com');

        $message = $this->messageAddressedTo($user->gt_email);
        $message->addBcc('george.burdell@gmail.com');

        $this->apply($message);

        $this->assertSame([], $message->getCc());
    }

    public function test_message_with_multiple_recipients_is_not_copied(): void
    {
        $first = $this->userWithGoogleAccount('gburdell3@gatech.edu', 'george.burdell@gmail.com');
        $second = $this->userWithGoogleAccount('gburdell4@gatech.edu', 'other.burdell@gmail.com');

        $message = new Email();
        $message->from('noreply@my.robojackets.org');
        $message->to(new Address($first->gt_email), new Address($second->gt_email));
        $message->subject('Test');
        $message->text('Hello');

        $this->apply($message);

        $this->assertSame([], $message->getCc());
    }

    public function test_address_that_is_not_a_user_is_not_copied(): void
    {
        $this->userWithGoogleAccount('gburdell3@gatech.edu', 'george.burdell@gmail.com');

        $message = $this->messageAddressedTo('sponsor@example.com');

        $this->apply($message);

        $this->assertSame([], $message->getCc());
    }

    public function test_ambiguous_recipient_is_not_copied(): void
    {
        $first = $this->userWithGoogleAccount('shared@gatech.edu', 'first@gmail.com');
        $second = User::factory()->create();
        $second->gt_email = $first->gt_email;
        $second->gmail_address = 'second@gmail.com';
        $second->save();

        $message = $this->messageAddressedTo($first->gt_email);

        $this->apply($message);

        $this->assertSame([], $message->getCc());
    }

    public function test_deleted_user_is_not_copied(): void
    {
        $user = $this->userWithGoogleAccount('gburdell3@gatech.edu', 'george.burdell@gmail.com');
        $user->delete();

        $message = $this->messageAddressedTo('gburdell3@gatech.edu');

        $this->apply($message);

        $this->assertSame([], $message->getCc());
    }

    private function userWithGoogleAccount(string $gtEmail, string $gmailAddress): User
    {
        $user = User::factory()->create([
            'first_name' => 'George',
            'preferred_name' => null,
            'last_name' => 'Burdell',
            'gt_email' => $gtEmail,
        ]);
        $user->gmail_address = $gmailAddress;
        $user->save();

        return $user;
    }

    private function messageAddressedTo(string $address, string $name = ''): Email
    {
        $message = new Email();
        $message->from('noreply@my.robojackets.org');
        $message->to(new Address($address, $name));
        $message->subject('Test');
        $message->text('Hello');

        return $message;
    }

    private function apply(Email $message): void
    {
        (new CcLinkedGoogleAccount())->handle(new MessageSending($message));
    }

    /**
     * Email addresses from Symfony address objects.
     *
     * @param  array<int, Address>  $addresses
     * @return array<int, string>
     */
    private function addresses(array $addresses): array
    {
        return array_map(
            static fn (Address $address): string => $address->getAddress(),
            $addresses
        );
    }

    /**
     * Carbon-copy addresses on the message.
     *
     * @return array<int, string>
     */
    private function ccAddresses(Email $message): array
    {
        return $this->addresses($message->getCc());
    }
}
