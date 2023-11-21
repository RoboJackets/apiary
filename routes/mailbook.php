<?php

declare(strict_types=1);

// @phan-file-suppress PhanTypeMismatchPropertyProbablyReal

use App\Models\DocuSignEnvelope;
use App\Models\DuesPackage;
use App\Models\DuesTransaction;
use App\Models\MembershipAgreementTemplate;
use App\Models\Payment;
use App\Models\Signature;
use App\Models\Travel;
use App\Models\TravelAssignment;
use App\Models\User;
use App\Notifications\Dues\PaymentReminder;
use App\Notifications\Dues\TransactionReminder;
use App\Notifications\ExpiringPersonalAccessTokenNotification;
use App\Notifications\MembershipAgreementDocuSignEnvelopeReceived;
use App\Notifications\PaymentReceipt;
use App\Notifications\Travel\AllTravelAssignmentsComplete;
use App\Notifications\Travel\DocuSignEnvelopeReceived;
use App\Notifications\Travel\TravelAssignmentCreated;
use App\Notifications\Travel\TravelAssignmentReminder;
use Carbon\Carbon;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Laravel\Passport\ClientRepository;
use Xammie\Mailbook\Facades\Mailbook;

Config::set('app.name', 'MyRoboJackets');
Config::set('features.sandbox-mode', true);
Config::set('database.default', 'sqlite');
Config::set('passport.storage.database.connection', 'sqlite');
Config::set('database.connections.sqlite.database', ':memory:');
Artisan::call('migrate');

$client = (new ClientRepository())->createPersonalAccessClient(null, 'test', 'http://localhost');

Config::set('passport.personal_access_client.id', $client->id);
Config::set('passport.personal_access_client.secret', $client->plain_secret);

$clientRepository = new ClientRepository($client->id, $client->plain_secret);

Container::getInstance()->bind(ClientRepository::class, static fn () => $clientRepository);

$user = User::withoutEvents(static function (): User {
    $user = User::factory()->make([
        'first_name' => 'George',
        'preferred_name' => null,
        'last_name' => 'Burdell',
        'gt_email' => 'george.burdell@gatech.edu',
        'primary_affiliation' => 'student',
    ]);
    $user->save();

    return $user;
});

Mailbook::to($user)
    ->label('Membership Agreement DocuSigned')
    ->add(static function (): MembershipAgreementDocuSignEnvelopeReceived {
        $user = User::withoutEvents(static function (): User {
            $user = User::factory()->make([
                'first_name' => 'George',
                'preferred_name' => null,
                'last_name' => 'Burdell',
                'gt_email' => 'george.burdell@gatech.edu',
            ]);
            $user->save();

            return $user;
        });

        MembershipAgreementTemplate::factory()->create();

        $signature = Signature::withoutEvents(static function () use ($user): Signature {
            $signature = Signature::factory()->make([
                'electronic' => true,
                'user_id' => $user->id,
            ]);
            $signature->save();

            return $signature;
        });

        $envelope = DocuSignEnvelope::withoutEvents(static function () use ($signature): DocuSignEnvelope {
            $envelope = new DocuSignEnvelope();
            $envelope->signable_type = $signature->getMorphClass();
            $envelope->signable_id = $signature->id;
            $envelope->signer_ip_address = '127.0.0.1';
            $envelope->signed_by = $signature->user->id;
            $envelope->envelope_id = bin2hex(openssl_random_pseudo_bytes(16));
            $envelope->save();

            return $envelope;
        });

        return new MembershipAgreementDocuSignEnvelopeReceived($envelope);
    });

Mailbook::to($user)
    ->add(TransactionReminder::class)
    ->label('Dues Transaction Reminder')
    ->variant('One Available Package', static function (): TransactionReminder {
        DuesPackage::withoutEvents(static fn (): DuesPackage => DuesPackage::firstOrCreate([
            'name' => 'Spring 2023',
        ], [
            'cost' => 55,
            'restricted_to_students' => true,
            'available_for_purchase' => true,
            'effective_end' => now()->addDays(1),
        ]));

        return new TransactionReminder();
    })
    ->variant('Two Available Packages', static function (): TransactionReminder {
        DuesPackage::withoutEvents(static fn (): DuesPackage => DuesPackage::firstOrCreate([
            'name' => 'Fall 2022',
            'cost' => 55,
            'restricted_to_students' => true,
        ]));

        DuesPackage::withoutEvents(static fn (): DuesPackage => DuesPackage::updateOrCreate([
            'name' => 'Full Year (2022-2023)',
        ], [
            'cost' => 100,
            'restricted_to_students' => true,
            'effective_end' => now()->addHours(1),
            'available_for_purchase' => true,
        ]));

        return new TransactionReminder();
    });

Mailbook::to($user)
    ->add(PaymentReminder::class)
    ->label('Dues Payment Reminder')
    ->variant('No Other Package', static function (): PaymentReminder {
        $user = User::withoutEvents(static function (): User {
            $user = User::factory()->make([
                'first_name' => 'George',
                'preferred_name' => null,
                'last_name' => 'Burdell',
                'gt_email' => 'george.burdell@gatech.edu',
            ]);
            $user->save();

            return $user;
        });

        $package = DuesPackage::withoutEvents(static fn (): DuesPackage => DuesPackage::firstOrCreate([
            'name' => 'Spring 2023',
            'cost' => 55,
        ]));

        $transaction = DuesTransaction::withoutEvents(static function () use ($user, $package): DuesTransaction {
            $transaction = DuesTransaction::factory()->make([
                'user_id' => $user->id,
                'dues_package_id' => $package->id,
            ]);
            $transaction->save();

            return $transaction;
        });

        return new PaymentReminder($transaction);
    })
    ->variant('One Other Package', static function (): PaymentReminder {
        $user = User::withoutEvents(static function (): User {
            $user = User::factory()->make([
                'first_name' => 'George',
                'preferred_name' => null,
                'last_name' => 'Burdell',
                'gt_email' => 'george.burdell@gatech.edu',
                'primary_affiliation' => 'student',
            ]);
            $user->save();

            return $user;
        });

        $package = DuesPackage::withoutEvents(static fn (): DuesPackage => DuesPackage::firstOrCreate([
            'name' => 'Spring 2023',
            'cost' => 55,
            'restricted_to_students' => true,
        ]));

        DuesPackage::withoutEvents(static fn (): DuesPackage => DuesPackage::updateOrCreate([
            'name' => 'Full Year (2022-2023)',
        ], [
            'cost' => 100,
            'restricted_to_students' => true,
            'effective_end' => now()->addHours(1),
            'available_for_purchase' => true,
        ]));

        $transaction = DuesTransaction::withoutEvents(static function () use ($user, $package): DuesTransaction {
            $transaction = DuesTransaction::factory()->make([
                'user_id' => $user->id,
                'dues_package_id' => $package->id,
            ]);
            $transaction->save();

            return $transaction;
        });

        return new PaymentReminder($transaction);
    });

Mailbook::to($user)
    ->add(PaymentReceipt::class)
    ->variant('Dues - Square', static function (): PaymentReceipt {
        $user = User::withoutEvents(static function (): User {
            $user = User::factory()->make([
                'first_name' => 'George',
                'preferred_name' => null,
                'last_name' => 'Burdell',
                'gt_email' => 'george.burdell@gatech.edu',
            ]);
            $user->save();

            return $user;
        });

        $package = DuesPackage::withoutEvents(static fn (): DuesPackage => DuesPackage::firstOrCreate([
            'name' => 'Fall 2022',
            'cost' => 55,
        ]));

        $transaction = DuesTransaction::withoutEvents(static function () use ($user, $package): DuesTransaction {
            $transaction = DuesTransaction::factory()->make([
                'user_id' => $user->id,
                'dues_package_id' => $package->id,
            ]);
            $transaction->save();

            return $transaction;
        });

        $payment = Payment::withoutEvents(static function () use ($transaction): Payment {
            $payment = new Payment();
            $payment->payable_type = $transaction->getMorphClass();
            $payment->payable_id = $transaction->id;
            $payment->amount = 56.95;
            $payment->method = 'square';
            $payment->receipt_url = 'https://example.com/'.Str::random(8);
            $payment->save();

            return $payment;
        });

        return new PaymentReceipt($payment);
    })
    ->variant('Dues - Cash', static function (): PaymentReceipt {
        $user = User::withoutEvents(static function (): User {
            $user = User::factory()->make([
                'first_name' => 'George',
                'preferred_name' => null,
                'last_name' => 'Burdell',
                'gt_email' => 'george.burdell@gatech.edu',
            ]);
            $user->save();

            return $user;
        });

        $officer = User::withoutEvents(static function (): User {
            $officer = User::factory()->make([
                'first_name' => 'Robo',
                'preferred_name' => null,
                'last_name' => 'Buzz',
                'gt_email' => 'robo.buzz@gatech.edu',
            ]);
            $officer->save();

            return $officer;
        });

        $package = DuesPackage::withoutEvents(static fn (): DuesPackage => DuesPackage::firstOrCreate([
            'name' => 'Fall 2022',
            'cost' => 55,
        ]));

        $transaction = DuesTransaction::withoutEvents(static function () use ($user, $package): DuesTransaction {
            $transaction = DuesTransaction::factory()->make([
                'user_id' => $user->id,
                'dues_package_id' => $package->id,
            ]);
            $transaction->save();

            return $transaction;
        });

        $payment = Payment::withoutEvents(static function () use ($transaction, $officer): Payment {
            $payment = new Payment();
            $payment->payable_type = $transaction->getMorphClass();
            $payment->payable_id = $transaction->id;
            $payment->amount = 55;
            $payment->method = 'cash';
            $payment->recorded_by = $officer->id;
            $payment->save();

            return $payment;
        });

        return new PaymentReceipt($payment);
    })
    ->variant('Travel - Square - Need Forms', static function (): PaymentReceipt {
        $user = User::withoutEvents(static function (): User {
            $user = User::factory()->make([
                'first_name' => 'George',
                'preferred_name' => null,
                'last_name' => 'Burdell',
                'gt_email' => 'george.burdell@gatech.edu',
            ]);
            $user->save();

            return $user;
        });

        $officer = User::withoutEvents(static function (): User {
            $officer = User::factory()->make([
                'first_name' => 'Robo',
                'preferred_name' => null,
                'last_name' => 'Buzz',
                'gt_email' => 'robo.buzz@gatech.edu',
            ]);
            $officer->save();

            return $officer;
        });

        $travel = Travel::withoutEvents(static fn (): Travel => Travel::firstOrCreate([
            'name' => 'Motorama 2022',
        ], [
            'destination' => 'mailbook',
            'departure_date' => '2022-02-18',
            'return_date' => '2022-02-21',
            'fee_amount' => 100,
            'tar_required' => true,
            'primary_contact_user_id' => $officer->id,
            'included_with_fee' => 'mailbook',
            'is_international' => false,
        ]));

        $assignment = TravelAssignment::withoutEvents(static function () use ($user, $travel): TravelAssignment {
            $assignment = TravelAssignment::factory()->make([
                'user_id' => $user->id,
                'travel_id' => $travel->id,
            ]);
            $assignment->save();

            return $assignment;
        });

        $payment = Payment::withoutEvents(static function () use ($assignment): Payment {
            $payment = new Payment();
            $payment->payable_type = $assignment->getMorphClass();
            $payment->payable_id = $assignment->id;
            $payment->amount = 103.30;
            $payment->method = 'square';
            $payment->receipt_url = 'https://example.com/'.Str::random(8);
            $payment->save();

            return $payment;
        });

        return new PaymentReceipt($payment);
    })
    ->variant('Travel - Square - Don\'t Need Forms', static function (): PaymentReceipt {
        $user = User::withoutEvents(static function (): User {
            $user = User::factory()->make([
                'first_name' => 'George',
                'preferred_name' => null,
                'last_name' => 'Burdell',
                'gt_email' => 'george.burdell@gatech.edu',
            ]);
            $user->save();

            return $user;
        });

        $officer = User::withoutEvents(static function (): User {
            $officer = User::factory()->make([
                'first_name' => 'Robo',
                'preferred_name' => null,
                'last_name' => 'Buzz',
                'gt_email' => 'robo.buzz@gatech.edu',
            ]);
            $officer->save();

            return $officer;
        });

        $travel = Travel::withoutEvents(static fn (): Travel => Travel::firstOrCreate([
            'name' => 'Motorama 2022',
        ], [
            'destination' => 'mailbook',
            'departure_date' => '2022-02-18',
            'return_date' => '2022-02-21',
            'fee_amount' => 100,
            'tar_required' => false,
            'primary_contact_user_id' => $officer->id,
            'included_with_fee' => 'mailbook',
            'is_international' => false,
        ]));

        $assignment = TravelAssignment::withoutEvents(static function () use ($user, $travel): TravelAssignment {
            $assignment = TravelAssignment::factory()->make([
                'user_id' => $user->id,
                'travel_id' => $travel->id,
            ]);
            $assignment->save();

            return $assignment;
        });

        $payment = Payment::withoutEvents(static function () use ($assignment): Payment {
            $payment = new Payment();
            $payment->payable_type = $assignment->getMorphClass();
            $payment->payable_id = $assignment->id;
            $payment->amount = 56.95;
            $payment->method = 'square';
            $payment->receipt_url = 'https://example.com/'.Str::random(8);
            $payment->save();

            return $payment;
        });

        return new PaymentReceipt($payment);
    })
    ->variant('Travel - Cash - Need Forms', static function (): PaymentReceipt {
        $user = User::withoutEvents(static function (): User {
            $user = User::factory()->make([
                'first_name' => 'George',
                'preferred_name' => null,
                'last_name' => 'Burdell',
                'gt_email' => 'george.burdell@gatech.edu',
            ]);
            $user->save();

            return $user;
        });

        $officer = User::withoutEvents(static function (): User {
            $officer = User::factory()->make([
                'first_name' => 'Robo',
                'preferred_name' => null,
                'last_name' => 'Buzz',
                'gt_email' => 'robo.buzz@gatech.edu',
            ]);
            $officer->save();

            return $officer;
        });

        $travel = Travel::withoutEvents(static fn (): Travel => Travel::firstOrCreate([
            'name' => 'Motorama 2022',
        ], [
            'destination' => 'mailbook',
            'departure_date' => '2022-02-18',
            'return_date' => '2022-02-21',
            'fee_amount' => 100,
            'tar_required' => true,
            'primary_contact_user_id' => $officer->id,
            'included_with_fee' => 'mailbook',
            'is_international' => false,
        ]));

        $assignment = TravelAssignment::withoutEvents(static function () use ($user, $travel): TravelAssignment {
            $assignment = TravelAssignment::factory()->make([
                'user_id' => $user->id,
                'travel_id' => $travel->id,
            ]);
            $assignment->save();

            return $assignment;
        });

        $payment = Payment::withoutEvents(static function () use ($assignment, $officer): Payment {
            $payment = new Payment();
            $payment->payable_type = $assignment->getMorphClass();
            $payment->payable_id = $assignment->id;
            $payment->amount = 100;
            $payment->method = 'cash';
            $payment->recorded_by = $officer->id;
            $payment->save();

            return $payment;
        });

        return new PaymentReceipt($payment);
    })
    ->variant('Travel - Cash - Don\'t Need Forms', static function (): PaymentReceipt {
        $user = User::withoutEvents(static function (): User {
            $user = User::factory()->make([
                'first_name' => 'George',
                'preferred_name' => null,
                'last_name' => 'Burdell',
                'gt_email' => 'george.burdell@gatech.edu',
            ]);
            $user->save();

            return $user;
        });

        $officer = User::withoutEvents(static function (): User {
            $officer = User::factory()->make([
                'first_name' => 'Robo',
                'preferred_name' => null,
                'last_name' => 'Buzz',
                'gt_email' => 'robo.buzz@gatech.edu',
            ]);
            $officer->save();

            return $officer;
        });

        $travel = Travel::withoutEvents(static fn (): Travel => Travel::firstOrCreate([
            'name' => 'Motorama 2022',
        ], [
            'destination' => 'mailbook',
            'departure_date' => '2022-02-18',
            'return_date' => '2022-02-21',
            'fee_amount' => 100,
            'tar_required' => false,
            'primary_contact_user_id' => $officer->id,
            'included_with_fee' => 'mailbook',
            'is_international' => false,
        ]));

        $assignment = TravelAssignment::withoutEvents(static function () use ($user, $travel): TravelAssignment {
            $assignment = TravelAssignment::factory()->make([
                'user_id' => $user->id,
                'travel_id' => $travel->id,
            ]);
            $assignment->save();

            return $assignment;
        });

        $payment = Payment::withoutEvents(static function () use ($assignment, $officer): Payment {
            $payment = new Payment();
            $payment->payable_type = $assignment->getMorphClass();
            $payment->payable_id = $assignment->id;
            $payment->amount = 100;
            $payment->method = 'cash';
            $payment->recorded_by = $officer->id;
            $payment->save();

            return $payment;
        });

        return new PaymentReceipt($payment);
    });

Mailbook::to($user)
    ->add(TravelAssignmentCreated::class)
    ->variant('Need Forms', static function (): TravelAssignmentCreated {
        $user = User::withoutEvents(static function (): User {
            $user = User::factory()->make([
                'first_name' => 'George',
                'preferred_name' => null,
                'last_name' => 'Burdell',
                'gt_email' => 'george.burdell@gatech.edu',
                'primary_affiliation' => 'student',
                'emergency_contact_name' => 'asdf',
                'emergency_contact_phone' => 'asdf',
            ]);
            $user->save();

            return $user;
        });

        $officer = User::withoutEvents(static function (): User {
            $officer = User::factory()->make([
                'first_name' => 'Robo',
                'preferred_name' => null,
                'last_name' => 'Buzz',
                'gt_email' => 'robo.buzz@gatech.edu',
            ]);
            $officer->save();

            return $officer;
        });

        $travel = Travel::withoutEvents(static fn (): Travel => Travel::firstOrCreate([
            'name' => 'Motorama 2022',
        ], [
            'destination' => 'mailbook',
            'departure_date' => '2022-02-18',
            'return_date' => '2022-02-21',
            'fee_amount' => 20,
            'tar_required' => true,
            'primary_contact_user_id' => $officer->id,
            'included_with_fee' => 'mailbook',
            'is_international' => false,
        ]));

        $assignment = TravelAssignment::withoutEvents(static function () use ($user, $travel): TravelAssignment {
            $assignment = TravelAssignment::factory()->make([
                'user_id' => $user->id,
                'travel_id' => $travel->id,
            ]);
            $assignment->save();

            return $assignment;
        });

        return new TravelAssignmentCreated($assignment);
    })
    ->variant('Need Forms And Emergency Contact', static function (): TravelAssignmentCreated {
        $user = User::withoutEvents(static function (): User {
            $user = User::factory()->make([
                'first_name' => 'George',
                'preferred_name' => null,
                'last_name' => 'Burdell',
                'gt_email' => 'george.burdell@gatech.edu',
                'primary_affiliation' => 'student',
            ]);
            $user->save();

            return $user;
        });

        $officer = User::withoutEvents(static function (): User {
            $officer = User::factory()->make([
                'first_name' => 'Robo',
                'preferred_name' => null,
                'last_name' => 'Buzz',
                'gt_email' => 'robo.buzz@gatech.edu',
            ]);
            $officer->save();

            return $officer;
        });

        $travel = Travel::withoutEvents(static fn (): Travel => Travel::firstOrCreate([
            'name' => 'Motorama 2022',
        ], [
            'destination' => 'mailbook',
            'departure_date' => '2022-02-18',
            'return_date' => '2022-02-21',
            'fee_amount' => 20,
            'tar_required' => true,
            'primary_contact_user_id' => $officer->id,
            'included_with_fee' => 'mailbook',
            'is_international' => false,
        ]));

        $assignment = TravelAssignment::withoutEvents(static function () use ($user, $travel): TravelAssignment {
            $assignment = TravelAssignment::factory()->make([
                'user_id' => $user->id,
                'travel_id' => $travel->id,
            ]);
            $assignment->save();

            return $assignment;
        });

        return new TravelAssignmentCreated($assignment);
    })
    ->variant('Need Emergency Contact', static function (): TravelAssignmentCreated {
        $user = User::withoutEvents(static function (): User {
            $user = User::factory()->make([
                'first_name' => 'George',
                'preferred_name' => null,
                'last_name' => 'Burdell',
                'gt_email' => 'george.burdell@gatech.edu',
                'primary_affiliation' => 'student',
            ]);
            $user->save();

            return $user;
        });

        $officer = User::withoutEvents(static function (): User {
            $officer = User::factory()->make([
                'first_name' => 'Robo',
                'preferred_name' => null,
                'last_name' => 'Buzz',
                'gt_email' => 'robo.buzz@gatech.edu',
            ]);
            $officer->save();

            return $officer;
        });

        $travel = Travel::withoutEvents(static fn (): Travel => Travel::firstOrCreate([
            'name' => 'Motorama 2022',
        ], [
            'destination' => 'mailbook',
            'departure_date' => '2022-02-18',
            'return_date' => '2022-02-21',
            'fee_amount' => 20,
            'tar_required' => false,
            'primary_contact_user_id' => $officer->id,
            'included_with_fee' => 'mailbook',
            'is_international' => false,
        ]));

        $assignment = TravelAssignment::withoutEvents(static function () use ($user, $travel): TravelAssignment {
            $assignment = TravelAssignment::factory()->make([
                'user_id' => $user->id,
                'travel_id' => $travel->id,
            ]);
            $assignment->save();

            return $assignment;
        });

        return new TravelAssignmentCreated($assignment);
    })
    ->variant('Need Payment', static function (): TravelAssignmentCreated {
        $user = User::withoutEvents(static function (): User {
            $user = User::factory()->make([
                'first_name' => 'George',
                'preferred_name' => null,
                'last_name' => 'Burdell',
                'gt_email' => 'george.burdell@gatech.edu',
                'primary_affiliation' => 'student',
                'emergency_contact_name' => 'asdf',
                'emergency_contact_phone' => 'asdf',
            ]);
            $user->save();

            return $user;
        });

        $officer = User::withoutEvents(static function (): User {
            $officer = User::factory()->make([
                'first_name' => 'Robo',
                'preferred_name' => null,
                'last_name' => 'Buzz',
                'gt_email' => 'robo.buzz@gatech.edu',
            ]);
            $officer->save();

            return $officer;
        });

        $travel = Travel::withoutEvents(static fn (): Travel => Travel::firstOrCreate([
            'name' => 'Motorama 2022',
        ], [
            'destination' => 'mailbook',
            'departure_date' => '2022-02-18',
            'return_date' => '2022-02-21',
            'fee_amount' => 20,
            'tar_required' => false,
            'primary_contact_user_id' => $officer->id,
            'included_with_fee' => 'mailbook',
            'is_international' => false,
        ]));

        $assignment = TravelAssignment::withoutEvents(static function () use ($user, $travel): TravelAssignment {
            $assignment = TravelAssignment::factory()->make([
                'user_id' => $user->id,
                'travel_id' => $travel->id,
            ]);
            $assignment->save();

            return $assignment;
        });

        return new TravelAssignmentCreated($assignment);
    });

Mailbook::to($user)
    ->add(TravelAssignmentReminder::class)
    ->variant('Need Forms', static function (): TravelAssignmentReminder {
        $user = User::withoutEvents(static function (): User {
            $user = User::factory()->make([
                'first_name' => 'George',
                'preferred_name' => null,
                'last_name' => 'Burdell',
                'gt_email' => 'george.burdell@gatech.edu',
                'primary_affiliation' => 'student',
                'emergency_contact_name' => 'asdf',
                'emergency_contact_phone' => 'asdf',
            ]);
            $user->save();

            return $user;
        });

        $officer = User::withoutEvents(static function (): User {
            $officer = User::factory()->make([
                'first_name' => 'Robo',
                'preferred_name' => null,
                'last_name' => 'Buzz',
                'gt_email' => 'robo.buzz@gatech.edu',
            ]);
            $officer->save();

            return $officer;
        });

        $travel = Travel::withoutEvents(static fn (): Travel => Travel::firstOrCreate([
            'name' => 'Motorama 2022',
        ], [
            'destination' => 'mailbook',
            'departure_date' => '2022-02-18',
            'return_date' => '2022-02-21',
            'fee_amount' => 20,
            'tar_required' => true,
            'primary_contact_user_id' => $officer->id,
            'included_with_fee' => 'mailbook',
            'is_international' => false,
        ]));

        $assignment = TravelAssignment::withoutEvents(static function () use ($user, $travel): TravelAssignment {
            $assignment = TravelAssignment::factory()->make([
                'user_id' => $user->id,
                'travel_id' => $travel->id,
            ]);
            $assignment->save();

            return $assignment;
        });

        Payment::withoutEvents(static function () use ($assignment, $officer): Payment {
            $payment = new Payment();
            $payment->payable_type = $assignment->getMorphClass();
            $payment->payable_id = $assignment->id;
            $payment->amount = 100;
            $payment->method = 'cash';
            $payment->recorded_by = $officer->id;
            $payment->save();

            return $payment;
        });

        return new TravelAssignmentReminder($assignment);
    })
    ->variant('Need Forms And Emergency Contact', static function (): TravelAssignmentReminder {
        $user = User::withoutEvents(static function (): User {
            $user = User::factory()->make([
                'first_name' => 'George',
                'preferred_name' => null,
                'last_name' => 'Burdell',
                'gt_email' => 'george.burdell@gatech.edu',
                'primary_affiliation' => 'student',
            ]);
            $user->save();

            return $user;
        });

        $officer = User::withoutEvents(static function (): User {
            $officer = User::factory()->make([
                'first_name' => 'Robo',
                'preferred_name' => null,
                'last_name' => 'Buzz',
                'gt_email' => 'robo.buzz@gatech.edu',
            ]);
            $officer->save();

            return $officer;
        });

        $travel = Travel::withoutEvents(static fn (): Travel => Travel::firstOrCreate([
            'name' => 'Motorama 2022',
        ], [
            'destination' => 'mailbook',
            'departure_date' => '2022-02-18',
            'return_date' => '2022-02-21',
            'fee_amount' => 20,
            'tar_required' => true,
            'primary_contact_user_id' => $officer->id,
            'included_with_fee' => 'mailbook',
            'is_international' => false,
        ]));

        $assignment = TravelAssignment::withoutEvents(static function () use ($user, $travel): TravelAssignment {
            $assignment = TravelAssignment::factory()->make([
                'user_id' => $user->id,
                'travel_id' => $travel->id,
            ]);
            $assignment->save();

            return $assignment;
        });

        Payment::withoutEvents(static function () use ($assignment, $officer): Payment {
            $payment = new Payment();
            $payment->payable_type = $assignment->getMorphClass();
            $payment->payable_id = $assignment->id;
            $payment->amount = 100;
            $payment->method = 'cash';
            $payment->recorded_by = $officer->id;
            $payment->save();

            return $payment;
        });

        return new TravelAssignmentReminder($assignment);
    })
    ->variant('Need Forms and Payment', static function (): TravelAssignmentReminder {
        $user = User::withoutEvents(static function (): User {
            $user = User::factory()->make([
                'first_name' => 'George',
                'preferred_name' => null,
                'last_name' => 'Burdell',
                'gt_email' => 'george.burdell@gatech.edu',
                'primary_affiliation' => 'student',
                'emergency_contact_name' => 'asdf',
                'emergency_contact_phone' => 'asdf',
            ]);
            $user->save();

            return $user;
        });

        $officer = User::withoutEvents(static function (): User {
            $officer = User::factory()->make([
                'first_name' => 'Robo',
                'preferred_name' => null,
                'last_name' => 'Buzz',
                'gt_email' => 'robo.buzz@gatech.edu',
            ]);
            $officer->save();

            return $officer;
        });

        $travel = Travel::withoutEvents(static fn (): Travel => Travel::firstOrCreate([
            'name' => 'Motorama 2022',
        ], [
            'destination' => 'mailbook',
            'departure_date' => '2022-02-18',
            'return_date' => '2022-02-21',
            'fee_amount' => 20,
            'tar_required' => true,
            'primary_contact_user_id' => $officer->id,
            'included_with_fee' => 'mailbook',
            'is_international' => false,
        ]));

        $assignment = TravelAssignment::withoutEvents(static function () use ($user, $travel): TravelAssignment {
            $assignment = TravelAssignment::factory()->make([
                'user_id' => $user->id,
                'travel_id' => $travel->id,
            ]);
            $assignment->save();

            return $assignment;
        });

        return new TravelAssignmentReminder($assignment);
    })

    ->variant('Need All Three', static function (): TravelAssignmentReminder {
        $user = User::withoutEvents(static function (): User {
            $user = User::factory()->make([
                'first_name' => 'George',
                'preferred_name' => null,
                'last_name' => 'Burdell',
                'gt_email' => 'george.burdell@gatech.edu',
                'primary_affiliation' => 'student',
            ]);
            $user->save();

            return $user;
        });

        $officer = User::withoutEvents(static function (): User {
            $officer = User::factory()->make([
                'first_name' => 'Robo',
                'preferred_name' => null,
                'last_name' => 'Buzz',
                'gt_email' => 'robo.buzz@gatech.edu',
            ]);
            $officer->save();

            return $officer;
        });

        $travel = Travel::withoutEvents(static fn (): Travel => Travel::firstOrCreate([
            'name' => 'Motorama 2022',
        ], [
            'destination' => 'mailbook',
            'departure_date' => '2022-02-18',
            'return_date' => '2022-02-21',
            'fee_amount' => 20,
            'tar_required' => true,
            'primary_contact_user_id' => $officer->id,
            'included_with_fee' => 'mailbook',
            'is_international' => false,
        ]));

        $assignment = TravelAssignment::withoutEvents(static function () use ($user, $travel): TravelAssignment {
            $assignment = TravelAssignment::factory()->make([
                'user_id' => $user->id,
                'travel_id' => $travel->id,
            ]);
            $assignment->save();

            return $assignment;
        });

        return new TravelAssignmentReminder($assignment);
    })
    ->variant('Need Payment', static function (): TravelAssignmentReminder {
        $user = User::withoutEvents(static function (): User {
            $user = User::factory()->make([
                'first_name' => 'George',
                'preferred_name' => null,
                'last_name' => 'Burdell',
                'gt_email' => 'george.burdell@gatech.edu',
                'primary_affiliation' => 'student',
                'emergency_contact_name' => 'asdf',
                'emergency_contact_phone' => 'asdf',
            ]);
            $user->save();

            return $user;
        });

        $officer = User::withoutEvents(static function (): User {
            $officer = User::factory()->make([
                'first_name' => 'Robo',
                'preferred_name' => null,
                'last_name' => 'Buzz',
                'gt_email' => 'robo.buzz@gatech.edu',
            ]);
            $officer->save();

            return $officer;
        });

        $travel = Travel::withoutEvents(static fn (): Travel => Travel::firstOrCreate([
            'name' => 'Motorama 2022',
        ], [
            'destination' => 'mailbook',
            'departure_date' => '2022-02-18',
            'return_date' => '2022-02-21',
            'fee_amount' => 20,
            'tar_required' => false,
            'primary_contact_user_id' => $officer->id,
            'included_with_fee' => 'mailbook',
            'is_international' => false,
        ]));

        $assignment = TravelAssignment::withoutEvents(static function () use ($user, $travel): TravelAssignment {
            $assignment = TravelAssignment::factory()->make([
                'user_id' => $user->id,
                'travel_id' => $travel->id,
            ]);
            $assignment->save();

            return $assignment;
        });

        return new TravelAssignmentReminder($assignment);
    })
    ->variant('Need Emergency Contact', static function (): TravelAssignmentReminder {
        $user = User::withoutEvents(static function (): User {
            $user = User::factory()->make([
                'first_name' => 'George',
                'preferred_name' => null,
                'last_name' => 'Burdell',
                'gt_email' => 'george.burdell@gatech.edu',
                'primary_affiliation' => 'student',
            ]);
            $user->save();

            return $user;
        });

        $officer = User::withoutEvents(static function (): User {
            $officer = User::factory()->make([
                'first_name' => 'Robo',
                'preferred_name' => null,
                'last_name' => 'Buzz',
                'gt_email' => 'robo.buzz@gatech.edu',
            ]);
            $officer->save();

            return $officer;
        });

        $travel = Travel::withoutEvents(static fn (): Travel => Travel::firstOrCreate([
            'name' => 'Motorama 2022',
        ], [
            'destination' => 'mailbook',
            'departure_date' => '2022-02-18',
            'return_date' => Carbon::now()->addDay(),
            'fee_amount' => 20,
            'tar_required' => false,
            'primary_contact_user_id' => $officer->id,
            'included_with_fee' => 'mailbook',
            'is_international' => false,
        ]));

        $assignment = TravelAssignment::withoutEvents(static function () use ($user, $travel): TravelAssignment {
            $assignment = TravelAssignment::factory()->make([
                'user_id' => $user->id,
                'travel_id' => $travel->id,
            ]);
            $assignment->save();

            return $assignment;
        });

        Payment::withoutEvents(static function () use ($assignment, $officer): Payment {
            $payment = new Payment();
            $payment->payable_type = $assignment->getMorphClass();
            $payment->payable_id = $assignment->id;
            $payment->amount = 100;
            $payment->method = 'cash';
            $payment->recorded_by = $officer->id;
            $payment->save();

            return $payment;
        });

        return new TravelAssignmentReminder($assignment);
    })
    ->variant('Need Emergency Contact And Payment', static function (): TravelAssignmentReminder {
        $user = User::withoutEvents(static function (): User {
            $user = User::factory()->make([
                'first_name' => 'George',
                'preferred_name' => null,
                'last_name' => 'Burdell',
                'gt_email' => 'george.burdell@gatech.edu',
                'primary_affiliation' => 'student',
            ]);
            $user->save();

            return $user;
        });

        $officer = User::withoutEvents(static function (): User {
            $officer = User::factory()->make([
                'first_name' => 'Robo',
                'preferred_name' => null,
                'last_name' => 'Buzz',
                'gt_email' => 'robo.buzz@gatech.edu',
            ]);
            $officer->save();

            return $officer;
        });

        $travel = Travel::withoutEvents(static fn (): Travel => Travel::firstOrCreate([
            'name' => 'Motorama 2022',
        ], [
            'destination' => 'mailbook',
            'departure_date' => '2022-02-18',
            'return_date' => Carbon::now()->addDay(),
            'fee_amount' => 20,
            'tar_required' => false,
            'primary_contact_user_id' => $officer->id,
            'included_with_fee' => 'mailbook',
            'is_international' => false,
        ]));

        $assignment = TravelAssignment::withoutEvents(static function () use ($user, $travel): TravelAssignment {
            $assignment = TravelAssignment::factory()->make([
                'user_id' => $user->id,
                'travel_id' => $travel->id,
            ]);
            $assignment->save();

            return $assignment;
        });

        return new TravelAssignmentReminder($assignment);
    });

Mailbook::to($user)
    ->add(DocuSignEnvelopeReceived::class)
    ->label('Travel Forms Received')
    ->variant('Need Payment', static function (): DocuSignEnvelopeReceived {
        $user = User::withoutEvents(static function (): User {
            $user = User::factory()->make([
                'first_name' => 'George',
                'preferred_name' => null,
                'last_name' => 'Burdell',
                'gt_email' => 'george.burdell@gatech.edu',
                'primary_affiliation' => 'student',
                'emergency_contact_name' => 'asdf',
                'emergency_contact_phone' => 'asdf',
            ]);
            $user->save();

            return $user;
        });

        $officer = User::withoutEvents(static function (): User {
            $officer = User::factory()->make([
                'first_name' => 'Robo',
                'preferred_name' => null,
                'last_name' => 'Buzz',
                'gt_email' => 'robo.buzz@gatech.edu',
            ]);
            $officer->save();

            return $officer;
        });

        $travel = Travel::withoutEvents(static fn (): Travel => Travel::firstOrCreate([
            'name' => 'Motorama 2022',
        ], [
            'destination' => 'mailbook',
            'departure_date' => '2022-02-18',
            'return_date' => '2022-02-21',
            'fee_amount' => 20,
            'tar_required' => false,
            'primary_contact_user_id' => $officer->id,
            'included_with_fee' => 'mailbook',
            'is_international' => false,
        ]));

        $assignment = TravelAssignment::withoutEvents(static function () use ($user, $travel): TravelAssignment {
            $assignment = TravelAssignment::factory()->make([
                'user_id' => $user->id,
                'travel_id' => $travel->id,
            ]);
            $assignment->save();

            return $assignment;
        });

        $envelope = DocuSignEnvelope::withoutEvents(static function () use ($assignment): DocuSignEnvelope {
            $envelope = new DocuSignEnvelope();
            $envelope->signable_type = $assignment->getMorphClass();
            $envelope->signable_id = $assignment->id;
            $envelope->signer_ip_address = '127.0.0.1';
            $envelope->signed_by = $assignment->user->id;
            $envelope->envelope_id = bin2hex(openssl_random_pseudo_bytes(16));
            $envelope->save();

            return $envelope;
        });

        return new DocuSignEnvelopeReceived($envelope);
    })
    ->variant('Need Payment And Emergency Contact', static function (): DocuSignEnvelopeReceived {
        $user = User::withoutEvents(static function (): User {
            $user = User::factory()->make([
                'first_name' => 'George',
                'preferred_name' => null,
                'last_name' => 'Burdell',
                'gt_email' => 'george.burdell@gatech.edu',
                'primary_affiliation' => 'student',
            ]);
            $user->save();

            return $user;
        });

        $officer = User::withoutEvents(static function (): User {
            $officer = User::factory()->make([
                'first_name' => 'Robo',
                'preferred_name' => null,
                'last_name' => 'Buzz',
                'gt_email' => 'robo.buzz@gatech.edu',
            ]);
            $officer->save();

            return $officer;
        });

        $travel = Travel::withoutEvents(static fn (): Travel => Travel::firstOrCreate([
            'name' => 'Motorama 2022',
        ], [
            'destination' => 'mailbook',
            'departure_date' => '2022-02-18',
            'return_date' => '2022-02-21',
            'fee_amount' => 20,
            'tar_required' => false,
            'primary_contact_user_id' => $officer->id,
            'included_with_fee' => 'mailbook',
            'is_international' => false,
        ]));

        $assignment = TravelAssignment::withoutEvents(static function () use ($user, $travel): TravelAssignment {
            $assignment = TravelAssignment::factory()->make([
                'user_id' => $user->id,
                'travel_id' => $travel->id,
            ]);
            $assignment->save();

            return $assignment;
        });

        $envelope = DocuSignEnvelope::withoutEvents(static function () use ($assignment): DocuSignEnvelope {
            $envelope = new DocuSignEnvelope();
            $envelope->signable_type = $assignment->getMorphClass();
            $envelope->signable_id = $assignment->id;
            $envelope->signer_ip_address = '127.0.0.1';
            $envelope->signed_by = $assignment->user->id;
            $envelope->envelope_id = bin2hex(openssl_random_pseudo_bytes(16));
            $envelope->save();

            return $envelope;
        });

        return new DocuSignEnvelopeReceived($envelope);
    })
    ->variant('Need Emergency Contact', static function (): DocuSignEnvelopeReceived {
        $user = User::withoutEvents(static function (): User {
            $user = User::factory()->make([
                'first_name' => 'George',
                'preferred_name' => null,
                'last_name' => 'Burdell',
                'gt_email' => 'george.burdell@gatech.edu',
                'primary_affiliation' => 'student',
            ]);
            $user->save();

            return $user;
        });

        $officer = User::withoutEvents(static function (): User {
            $officer = User::factory()->make([
                'first_name' => 'Robo',
                'preferred_name' => null,
                'last_name' => 'Buzz',
                'gt_email' => 'robo.buzz@gatech.edu',
            ]);
            $officer->save();

            return $officer;
        });

        $travel = Travel::withoutEvents(static fn (): Travel => Travel::firstOrCreate([
            'name' => 'Motorama 2022',
        ], [
            'destination' => 'mailbook',
            'departure_date' => '2022-02-18',
            'return_date' => '2022-02-21',
            'fee_amount' => 20,
            'tar_required' => false,
            'primary_contact_user_id' => $officer->id,
            'included_with_fee' => 'mailbook',
            'is_international' => false,
        ]));

        $assignment = TravelAssignment::withoutEvents(static function () use ($user, $travel): TravelAssignment {
            $assignment = TravelAssignment::factory()->make([
                'user_id' => $user->id,
                'travel_id' => $travel->id,
            ]);
            $assignment->save();

            return $assignment;
        });

        Payment::withoutEvents(static function () use ($assignment, $officer): Payment {
            $payment = new Payment();
            $payment->payable_type = $assignment->getMorphClass();
            $payment->payable_id = $assignment->id;
            $payment->amount = 20;
            $payment->method = 'cash';
            $payment->recorded_by = $officer->id;
            $payment->save();

            return $payment;
        });

        $envelope = DocuSignEnvelope::withoutEvents(static function () use ($assignment): DocuSignEnvelope {
            $envelope = new DocuSignEnvelope();
            $envelope->signable_type = $assignment->getMorphClass();
            $envelope->signable_id = $assignment->id;
            $envelope->signer_ip_address = '127.0.0.1';
            $envelope->signed_by = $assignment->user->id;
            $envelope->envelope_id = bin2hex(openssl_random_pseudo_bytes(16));
            $envelope->save();

            return $envelope;
        });

        return new DocuSignEnvelopeReceived($envelope);
    })
    ->variant('All Items Complete', static function (): DocuSignEnvelopeReceived {
        $user = User::withoutEvents(static function (): User {
            $user = User::factory()->make([
                'first_name' => 'George',
                'preferred_name' => null,
                'last_name' => 'Burdell',
                'gt_email' => 'george.burdell@gatech.edu',
                'primary_affiliation' => 'student',
                'emergency_contact_name' => 'asdf',
                'emergency_contact_phone' => 'asdf',
            ]);
            $user->save();

            return $user;
        });

        $officer = User::withoutEvents(static function (): User {
            $officer = User::factory()->make([
                'first_name' => 'Robo',
                'preferred_name' => null,
                'last_name' => 'Buzz',
                'gt_email' => 'robo.buzz@gatech.edu',
            ]);
            $officer->save();

            return $officer;
        });

        $travel = Travel::withoutEvents(static fn (): Travel => Travel::firstOrCreate([
            'name' => 'Motorama 2022',
        ], [
            'destination' => 'mailbook',
            'departure_date' => '2022-02-18',
            'return_date' => '2022-02-21',
            'fee_amount' => 20,
            'tar_required' => false,
            'primary_contact_user_id' => $officer->id,
            'included_with_fee' => 'mailbook',
            'is_international' => false,
        ]));

        $assignment = TravelAssignment::withoutEvents(static function () use ($user, $travel): TravelAssignment {
            $assignment = TravelAssignment::factory()->make([
                'user_id' => $user->id,
                'travel_id' => $travel->id,
            ]);
            $assignment->save();

            return $assignment;
        });

        Payment::withoutEvents(static function () use ($assignment, $officer): Payment {
            $payment = new Payment();
            $payment->payable_type = $assignment->getMorphClass();
            $payment->payable_id = $assignment->id;
            $payment->amount = 20;
            $payment->method = 'cash';
            $payment->recorded_by = $officer->id;
            $payment->save();

            return $payment;
        });

        $envelope = DocuSignEnvelope::withoutEvents(static function () use ($assignment): DocuSignEnvelope {
            $envelope = new DocuSignEnvelope();
            $envelope->signable_type = $assignment->getMorphClass();
            $envelope->signable_id = $assignment->id;
            $envelope->signer_ip_address = '127.0.0.1';
            $envelope->signed_by = $assignment->user->id;
            $envelope->envelope_id = bin2hex(openssl_random_pseudo_bytes(16));
            $envelope->save();

            return $envelope;
        });

        return new DocuSignEnvelopeReceived($envelope);
    });

Mailbook::to($user)
    ->add(AllTravelAssignmentsComplete::class)
    ->label('Travel Assignments Complete')
    ->variant('Forms and Payments', static function (): AllTravelAssignmentsComplete {
        $user = User::withoutEvents(static function (): User {
            $user = User::factory()->make([
                'first_name' => 'George',
                'preferred_name' => null,
                'last_name' => 'Burdell',
                'gt_email' => 'george.burdell@gatech.edu',
                'primary_affiliation' => 'student',
            ]);
            $user->save();

            return $user;
        });

        $officer = User::withoutEvents(static function (): User {
            $officer = User::factory()->make([
                'first_name' => 'Robo',
                'preferred_name' => null,
                'last_name' => 'Buzz',
                'gt_email' => 'robo.buzz@gatech.edu',
            ]);
            $officer->save();

            return $officer;
        });

        $travel = Travel::withoutEvents(static fn (): Travel => Travel::firstOrCreate([
            'name' => 'Motorama 2022',
        ], [
            'destination' => 'mailbook',
            'departure_date' => '2022-02-18',
            'return_date' => '2022-02-21',
            'fee_amount' => 20,
            'tar_required' => true,
            'primary_contact_user_id' => $officer->id,
            'included_with_fee' => 'mailbook',
            'is_international' => false,
        ]));

        $assignment = TravelAssignment::withoutEvents(static function () use ($user, $travel): TravelAssignment {
            $assignment = TravelAssignment::factory()->make([
                'user_id' => $user->id,
                'travel_id' => $travel->id,
                'tar_received' => true,
            ]);
            $assignment->save();

            return $assignment;
        });

        Payment::withoutEvents(static function () use ($assignment, $officer): Payment {
            $payment = new Payment();
            $payment->payable_type = $assignment->getMorphClass();
            $payment->payable_id = $assignment->id;
            $payment->amount = 20;
            $payment->method = 'cash';
            $payment->recorded_by = $officer->id;
            $payment->save();

            return $payment;
        });

        return new AllTravelAssignmentsComplete($travel);
    })
    ->variant('Forms Only - Need One Payment', static function (): AllTravelAssignmentsComplete {
        $user = User::withoutEvents(static function (): User {
            $user = User::factory()->make([
                'first_name' => 'George',
                'preferred_name' => null,
                'last_name' => 'Burdell',
                'gt_email' => 'george.burdell@gatech.edu',
                'primary_affiliation' => 'student',
            ]);
            $user->save();

            return $user;
        });

        $officer = User::withoutEvents(static function (): User {
            $officer = User::factory()->make([
                'first_name' => 'Robo',
                'preferred_name' => null,
                'last_name' => 'Buzz',
                'gt_email' => 'robo.buzz@gatech.edu',
            ]);
            $officer->save();

            return $officer;
        });

        $travel = Travel::withoutEvents(static fn (): Travel => Travel::firstOrCreate([
            'name' => 'Motorama 2022',
        ], [
            'destination' => 'mailbook',
            'departure_date' => '2022-02-18',
            'return_date' => '2022-02-21',
            'fee_amount' => 20,
            'tar_required' => true,
            'primary_contact_user_id' => $officer->id,
            'included_with_fee' => 'mailbook',
            'is_international' => false,
        ]));

        TravelAssignment::withoutEvents(static function () use ($user, $travel): TravelAssignment {
            $assignment = TravelAssignment::factory()->make([
                'user_id' => $user->id,
                'travel_id' => $travel->id,
                'tar_received' => true,
            ]);
            $assignment->save();

            return $assignment;
        });

        return new AllTravelAssignmentsComplete($travel);
    })
    ->variant('Forms Only - Need Two Payments', static function (): AllTravelAssignmentsComplete {
        $user = User::withoutEvents(static function (): User {
            $user = User::factory()->make([
                'first_name' => 'George',
                'preferred_name' => null,
                'last_name' => 'Burdell',
                'gt_email' => 'george.burdell@gatech.edu',
                'primary_affiliation' => 'student',
            ]);
            $user->save();

            return $user;
        });

        $otherUser = User::withoutEvents(static function (): User {
            $user = User::factory()->make([
                'first_name' => 'Georgia',
                'preferred_name' => null,
                'last_name' => 'Burdell',
                'gt_email' => 'georgia.burdell@gatech.edu',
                'primary_affiliation' => 'student',
            ]);
            $user->save();

            return $user;
        });

        $officer = User::withoutEvents(static function (): User {
            $officer = User::factory()->make([
                'first_name' => 'Robo',
                'preferred_name' => null,
                'last_name' => 'Buzz',
                'gt_email' => 'robo.buzz@gatech.edu',
            ]);
            $officer->save();

            return $officer;
        });

        $travel = Travel::withoutEvents(static fn (): Travel => Travel::firstOrCreate([
            'name' => 'Motorama 2022',
        ], [
            'destination' => 'mailbook',
            'departure_date' => '2022-02-18',
            'return_date' => '2022-02-21',
            'fee_amount' => 20,
            'tar_required' => true,
            'primary_contact_user_id' => $officer->id,
            'included_with_fee' => 'mailbook',
            'is_international' => false,
        ]));

        TravelAssignment::withoutEvents(static function () use ($user, $travel): TravelAssignment {
            $assignment = TravelAssignment::factory()->make([
                'user_id' => $user->id,
                'travel_id' => $travel->id,
                'tar_received' => true,
            ]);
            $assignment->save();

            return $assignment;
        });

        TravelAssignment::withoutEvents(static function () use ($otherUser, $travel): TravelAssignment {
            $assignment = TravelAssignment::factory()->make([
                'user_id' => $otherUser->id,
                'travel_id' => $travel->id,
                'tar_received' => true,
            ]);
            $assignment->save();

            return $assignment;
        });

        return new AllTravelAssignmentsComplete($travel);
    })
    ->variant('Payments Only - Need One Form', static function (): AllTravelAssignmentsComplete {
        $user = User::withoutEvents(static function (): User {
            $user = User::factory()->make([
                'first_name' => 'George',
                'preferred_name' => null,
                'last_name' => 'Burdell',
                'gt_email' => 'george.burdell@gatech.edu',
                'primary_affiliation' => 'student',
            ]);
            $user->save();

            return $user;
        });

        $otherUser = User::withoutEvents(static function (): User {
            $user = User::factory()->make([
                'first_name' => 'Georgia',
                'preferred_name' => null,
                'last_name' => 'Burdell',
                'gt_email' => 'georgia.burdell@gatech.edu',
                'primary_affiliation' => 'student',
            ]);
            $user->save();

            return $user;
        });

        $officer = User::withoutEvents(static function (): User {
            $officer = User::factory()->make([
                'first_name' => 'Robo',
                'preferred_name' => null,
                'last_name' => 'Buzz',
                'gt_email' => 'robo.buzz@gatech.edu',
            ]);
            $officer->save();

            return $officer;
        });

        $travel = Travel::withoutEvents(static fn (): Travel => Travel::firstOrCreate([
            'name' => 'Motorama 2022',
        ], [
            'destination' => 'mailbook',
            'departure_date' => '2022-02-18',
            'return_date' => '2022-02-21',
            'fee_amount' => 20,
            'tar_required' => true,
            'primary_contact_user_id' => $officer->id,
            'included_with_fee' => 'mailbook',
            'is_international' => false,
        ]));

        $assignment = TravelAssignment::withoutEvents(static function () use ($user, $travel): TravelAssignment {
            $assignment = TravelAssignment::factory()->make([
                'user_id' => $user->id,
                'travel_id' => $travel->id,
                'tar_received' => true,
            ]);
            $assignment->save();

            return $assignment;
        });

        $otherAssignment = TravelAssignment::withoutEvents(
            static function () use ($otherUser, $travel): TravelAssignment {
                $assignment = TravelAssignment::factory()->make([
                    'user_id' => $otherUser->id,
                    'travel_id' => $travel->id,
                    'tar_received' => false,
                ]);
                $assignment->save();

                return $assignment;
            }
        );

        Payment::withoutEvents(static function () use ($assignment, $officer): Payment {
            $payment = new Payment();
            $payment->payable_type = $assignment->getMorphClass();
            $payment->payable_id = $assignment->id;
            $payment->amount = 20;
            $payment->method = 'cash';
            $payment->recorded_by = $officer->id;
            $payment->save();

            return $payment;
        });

        Payment::withoutEvents(static function () use ($otherAssignment, $officer): Payment {
            $payment = new Payment();
            $payment->payable_type = $otherAssignment->getMorphClass();
            $payment->payable_id = $otherAssignment->id;
            $payment->amount = 20;
            $payment->method = 'cash';
            $payment->recorded_by = $officer->id;
            $payment->save();

            return $payment;
        });

        return new AllTravelAssignmentsComplete($travel);
    })
    ->variant('Payments Only - Need Two Forms', static function (): AllTravelAssignmentsComplete {
        $user = User::withoutEvents(static function (): User {
            $user = User::factory()->make([
                'first_name' => 'George',
                'preferred_name' => null,
                'last_name' => 'Burdell',
                'gt_email' => 'george.burdell@gatech.edu',
                'primary_affiliation' => 'student',
            ]);
            $user->save();

            return $user;
        });

        $otherUser = User::withoutEvents(static function (): User {
            $user = User::factory()->make([
                'first_name' => 'Georgia',
                'preferred_name' => null,
                'last_name' => 'Burdell',
                'gt_email' => 'georgia.burdell@gatech.edu',
                'primary_affiliation' => 'student',
            ]);
            $user->save();

            return $user;
        });

        $officer = User::withoutEvents(static function (): User {
            $officer = User::factory()->make([
                'first_name' => 'Robo',
                'preferred_name' => null,
                'last_name' => 'Buzz',
                'gt_email' => 'robo.buzz@gatech.edu',
            ]);
            $officer->save();

            return $officer;
        });

        $travel = Travel::withoutEvents(static fn (): Travel => Travel::firstOrCreate([
            'name' => 'Motorama 2022',
        ], [
            'destination' => 'mailbook',
            'departure_date' => '2022-02-18',
            'return_date' => '2022-02-21',
            'fee_amount' => 20,
            'tar_required' => true,
            'primary_contact_user_id' => $officer->id,
            'included_with_fee' => 'mailbook',
            'is_international' => false,
        ]));

        $assignment = TravelAssignment::withoutEvents(static function () use ($user, $travel): TravelAssignment {
            $assignment = TravelAssignment::factory()->make([
                'user_id' => $user->id,
                'travel_id' => $travel->id,
                'tar_received' => false,
            ]);
            $assignment->save();

            return $assignment;
        });

        $otherAssignment = TravelAssignment::withoutEvents(
            static function () use ($otherUser, $travel): TravelAssignment {
                $assignment = TravelAssignment::factory()->make([
                    'user_id' => $otherUser->id,
                    'travel_id' => $travel->id,
                    'tar_received' => false,
                ]);
                $assignment->save();

                return $assignment;
            }
        );

        Payment::withoutEvents(static function () use ($assignment, $officer): Payment {
            $payment = new Payment();
            $payment->payable_type = $assignment->getMorphClass();
            $payment->payable_id = $assignment->id;
            $payment->amount = 20;
            $payment->method = 'cash';
            $payment->recorded_by = $officer->id;
            $payment->save();

            return $payment;
        });

        Payment::withoutEvents(static function () use ($otherAssignment, $officer): Payment {
            $payment = new Payment();
            $payment->payable_type = $otherAssignment->getMorphClass();
            $payment->payable_id = $otherAssignment->id;
            $payment->amount = 20;
            $payment->method = 'cash';
            $payment->recorded_by = $officer->id;
            $payment->save();

            return $payment;
        });

        return new AllTravelAssignmentsComplete($travel);
    })
    ->variant('Payments Only - Don\'t Need Forms', static function (): AllTravelAssignmentsComplete {
        $user = User::withoutEvents(static function (): User {
            $user = User::factory()->make([
                'first_name' => 'George',
                'preferred_name' => null,
                'last_name' => 'Burdell',
                'gt_email' => 'george.burdell@gatech.edu',
                'primary_affiliation' => 'student',
            ]);
            $user->save();

            return $user;
        });

        $otherUser = User::withoutEvents(static function (): User {
            $user = User::factory()->make([
                'first_name' => 'Georgia',
                'preferred_name' => null,
                'last_name' => 'Burdell',
                'gt_email' => 'georgia.burdell@gatech.edu',
                'primary_affiliation' => 'student',
            ]);
            $user->save();

            return $user;
        });

        $officer = User::withoutEvents(static function (): User {
            $officer = User::factory()->make([
                'first_name' => 'Robo',
                'preferred_name' => null,
                'last_name' => 'Buzz',
                'gt_email' => 'robo.buzz@gatech.edu',
            ]);
            $officer->save();

            return $officer;
        });

        $travel = Travel::withoutEvents(static fn (): Travel => Travel::firstOrCreate([
            'name' => 'Motorama 2022',
        ], [
            'destination' => 'mailbook',
            'departure_date' => '2022-02-18',
            'return_date' => '2022-02-21',
            'fee_amount' => 20,
            'tar_required' => false,
            'primary_contact_user_id' => $officer->id,
            'included_with_fee' => 'mailbook',
            'is_international' => false,
        ]));

        $assignment = TravelAssignment::withoutEvents(static function () use ($user, $travel): TravelAssignment {
            $assignment = TravelAssignment::factory()->make([
                'user_id' => $user->id,
                'travel_id' => $travel->id,
                'tar_received' => false,
            ]);
            $assignment->save();

            return $assignment;
        });

        $otherAssignment = TravelAssignment::withoutEvents(
            static function () use ($otherUser, $travel): TravelAssignment {
                $assignment = TravelAssignment::factory()->make([
                    'user_id' => $otherUser->id,
                    'travel_id' => $travel->id,
                    'tar_received' => false,
                ]);
                $assignment->save();

                return $assignment;
            }
        );

        Payment::withoutEvents(static function () use ($assignment, $officer): Payment {
            $payment = new Payment();
            $payment->payable_type = $assignment->getMorphClass();
            $payment->payable_id = $assignment->id;
            $payment->amount = 20;
            $payment->method = 'cash';
            $payment->recorded_by = $officer->id;
            $payment->save();

            return $payment;
        });

        Payment::withoutEvents(static function () use ($otherAssignment, $officer): Payment {
            $payment = new Payment();
            $payment->payable_type = $otherAssignment->getMorphClass();
            $payment->payable_id = $otherAssignment->id;
            $payment->amount = 20;
            $payment->method = 'cash';
            $payment->recorded_by = $officer->id;
            $payment->save();

            return $payment;
        });

        return new AllTravelAssignmentsComplete($travel);
    });

Mailbook::to($user)
    ->add(ExpiringPersonalAccessTokenNotification::class)
    ->label('Expiring Personal Access Token')
    ->variant('Already Expired', static function (): ExpiringPersonalAccessTokenNotification {
        $user = User::withoutEvents(static function (): User {
            $user = User::factory()->make([
                'first_name' => 'George',
                'preferred_name' => null,
                'last_name' => 'Burdell',
                'gt_email' => 'george.burdell@gatech.edu',
            ]);
            $user->save();

            return $user;
        });

        $token = $user->createToken('email test token')->token;
        $token->expires_at = now()->subHours(1);
        $token->save();

        return new ExpiringPersonalAccessTokenNotification($token);
    })
    ->variant('Expiring Soon', static function (): ExpiringPersonalAccessTokenNotification {
        $user = User::withoutEvents(static function (): User {
            $user = User::factory()->make([
                'first_name' => 'George',
                'preferred_name' => null,
                'last_name' => 'Burdell',
                'gt_email' => 'george.burdell@gatech.edu',
            ]);
            $user->save();

            return $user;
        });

        $token = $user->createToken('email test token')->token;
        $token->expires_at = now()->addHours(1);
        $token->save();

        return new ExpiringPersonalAccessTokenNotification($token);
    });
