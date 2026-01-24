<?php

declare(strict_types=1);

// @phan-file-suppress PhanTypeMismatchPropertyProbablyReal

use App\Mail\DocuSign\Agreement\MemberNotification;
use App\Mail\DocuSign\Agreement\ParentNotification;
use App\Mail\DocuSign\Travel as TravelMail;
use App\Mail\Sponsors\SponsorOneTimePassword;
use App\Models\DocuSignEnvelope;
use App\Models\DuesPackage;
use App\Models\DuesTransaction;
use App\Models\MembershipAgreementTemplate;
use App\Models\Payment;
use App\Models\Signature;
use App\Models\Sponsor;
use App\Models\SponsorUser;
use App\Models\Travel;
use App\Models\TravelAssignment;
use App\Models\User;
use App\Notifications\Dues\PackageExpirationReminder;
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
use Spatie\OneTimePasswords\Models\OneTimePassword;
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

$clientRepository = new ClientRepository();

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

Mailbook::category('Membership Agreements')->group(static function () use ($user): void {
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

    Mailbook::to(null)
        ->add(MemberNotification::class)
        ->label('DocuSign Membership Agreement')
        ->variant('Member Notification', static function (): MemberNotification {
            $user = User::withoutEvents(static function (): User {
                $user = User::factory()->make([
                    'first_name' => 'George',
                    'preferred_name' => null,
                    'last_name' => 'Burdell',
                    'uid' => 'gburdell3',
                    'gt_email' => 'george.burdell@gatech.edu',
                ]);
                $user->save();

                return $user;
            });

            return new MemberNotification($user);
        })
        ->variant('Parent Notification', static function (): ParentNotification {
            $user = User::withoutEvents(static function (): User {
                $user = User::factory()->make([
                    'first_name' => 'George',
                    'preferred_name' => null,
                    'last_name' => 'Burdell',
                    'uid' => 'gburdell3',
                    'gt_email' => 'george.burdell@gatech.edu',
                ]);
                $user->save();

                return $user;
            });

            return new ParentNotification($user);
        });
});

Mailbook::category('Dues Reminders')->group(static function () use ($user): void {
    Mailbook::to($user)
        ->add(TransactionReminder::class)
        ->label('Dues Transaction Reminder')
        ->variant('One Available Package', static function (): TransactionReminder {
            DuesPackage::withoutEvents(static fn (): DuesPackage => DuesPackage::firstOrCreate([
                'name' => 'Spring 2023',
            ], [
                'cost' => 55,
                'restricted_to_students' => true,
                'effective_start' => now()->subHours(1),
                'effective_end' => now()->addDays(1),
                'available_for_purchase' => true,
            ]));

            return new TransactionReminder();
        })
        ->variant('Two Available Packages', static function (): TransactionReminder {
            DuesPackage::withoutEvents(static fn (): DuesPackage => DuesPackage::firstOrCreate([
                'name' => 'Fall 2022',
                'cost' => 55,
                'restricted_to_students' => true,
                'effective_start' => now()->subHours(1),
                'effective_end' => now()->addHours(1),
                'available_for_purchase' => true,
            ]));

            DuesPackage::withoutEvents(static fn (): DuesPackage => DuesPackage::updateOrCreate([
                'name' => 'Full Year (2022-2023)',
            ], [
                'cost' => 100,
                'restricted_to_students' => true,
                'effective_start' => now()->subHours(1),
                'effective_end' => now()->addHours(1),
                'available_for_purchase' => true,
            ]));

            return new TransactionReminder();
        })
        ->variant('Three Available Packages', static function (): TransactionReminder {
            DuesPackage::withoutEvents(static fn (): DuesPackage => DuesPackage::firstOrCreate([
                'name' => 'Fall 2025',
                'cost' => 55,
                'restricted_to_students' => true,
                'effective_start' => now()->subHours(1),
                'effective_end' => now()->addHours(1),
                'available_for_purchase' => true,
            ]));

            DuesPackage::withoutEvents(static fn (): DuesPackage => DuesPackage::firstOrCreate([
                'name' => 'Spring 2026',
                'cost' => 55,
                'restricted_to_students' => true,
                'effective_start' => now()->addHours(1),
                'effective_end' => now()->addHours(2),
                'available_for_purchase' => true,
            ]));

            DuesPackage::withoutEvents(static fn (): DuesPackage => DuesPackage::updateOrCreate([
                'name' => 'Full Year (2025-2026)',
            ], [
                'cost' => 100,
                'restricted_to_students' => true,
                'effective_start' => now()->subHours(1),
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
        })
        ->variant('Two Other Packages', static function (): PaymentReminder {
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
                'name' => 'Fall 2025',
                'cost' => 55,
                'restricted_to_students' => true,
                'effective_start' => now()->subHours(1),
                'effective_end' => now()->addHours(1),
                'available_for_purchase' => true,
            ]));

            DuesPackage::withoutEvents(static fn (): DuesPackage => DuesPackage::updateOrCreate([
                'name' => 'Spring 2026',
            ], [
                'cost' => 100,
                'restricted_to_students' => true,
                'effective_start' => now()->addHours(1),
                'effective_end' => now()->addHours(2),
                'available_for_purchase' => true,
            ]));

            DuesPackage::withoutEvents(static fn (): DuesPackage => DuesPackage::updateOrCreate([
                'name' => 'Full Year (2025-2026)',
            ], [
                'cost' => 100,
                'restricted_to_students' => true,
                'effective_start' => now()->subHours(1),
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
});

Mailbook::category('Payments')->group(static function () use ($user): void {
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
        ->variant('Trip Fee - Square - Need Travel Information Form', static function (): PaymentReceipt {
            $user = User::withoutEvents(static function (): User {
                $user = User::factory()->make([
                    'first_name' => 'George',
                    'preferred_name' => null,
                    'last_name' => 'Burdell',
                    'gt_email' => 'george.burdell@gatech.edu',
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
                'fee_amount' => 100,
                'forms' => [
                    Travel::TRAVEL_INFORMATION_FORM_KEY => true,
                ],
                'primary_contact_user_id' => $officer->id,
                'included_with_fee' => 'mailbook',
                'is_international' => false,
                'status' => 'draft',
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
        ->variant('Trip Fee - Square - Need Airfare Request Form', static function (): PaymentReceipt {
            $user = User::withoutEvents(static function (): User {
                $user = User::factory()->make([
                    'first_name' => 'George',
                    'preferred_name' => null,
                    'last_name' => 'Burdell',
                    'gt_email' => 'george.burdell@gatech.edu',
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
                'fee_amount' => 100,
                'forms' => [
                    Travel::AIRFARE_REQUEST_FORM_KEY => true,
                ],
                'primary_contact_user_id' => $officer->id,
                'included_with_fee' => 'mailbook',
                'is_international' => false,
                'status' => 'draft',
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
        ->variant('Trip Fee - Square - Need Both Forms', static function (): PaymentReceipt {
            $user = User::withoutEvents(static function (): User {
                $user = User::factory()->make([
                    'first_name' => 'George',
                    'preferred_name' => null,
                    'last_name' => 'Burdell',
                    'gt_email' => 'george.burdell@gatech.edu',
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
                'fee_amount' => 100,
                'forms' => [
                    Travel::TRAVEL_INFORMATION_FORM_KEY => true,
                    Travel::AIRFARE_REQUEST_FORM_KEY => true,
                ],
                'primary_contact_user_id' => $officer->id,
                'included_with_fee' => 'mailbook',
                'is_international' => false,
                'status' => 'draft',
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
        ->variant('Trip Fee - Square - Need Forms and Emergency Contact', static function (): PaymentReceipt {
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
                'return_date' => Carbon::now()->addDay(),
                'fee_amount' => 100,
                'forms' => [
                    Travel::TRAVEL_INFORMATION_FORM_KEY => true,
                    Travel::AIRFARE_REQUEST_FORM_KEY => true,
                ],
                'primary_contact_user_id' => $officer->id,
                'included_with_fee' => 'mailbook',
                'is_international' => false,
                'status' => 'draft',
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
        ->variant('Trip Fee - Square - All Items Complete', static function (): PaymentReceipt {
            $user = User::withoutEvents(static function (): User {
                $user = User::factory()->make([
                    'first_name' => 'George',
                    'preferred_name' => null,
                    'last_name' => 'Burdell',
                    'gt_email' => 'george.burdell@gatech.edu',
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
                'fee_amount' => 100,
                'primary_contact_user_id' => $officer->id,
                'included_with_fee' => 'mailbook',
                'is_international' => false,
                'status' => 'draft',
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
        ->variant('Trip Fee - Square - Need Emergency Contact', static function (): PaymentReceipt {
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
                'primary_contact_user_id' => $officer->id,
                'included_with_fee' => 'mailbook',
                'is_international' => false,
                'status' => 'draft',
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
        ->variant('Trip Fee - Cash - Need Travel Information Form', static function (): PaymentReceipt {
            $user = User::withoutEvents(static function (): User {
                $user = User::factory()->make([
                    'first_name' => 'George',
                    'preferred_name' => null,
                    'last_name' => 'Burdell',
                    'gt_email' => 'george.burdell@gatech.edu',
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
                'fee_amount' => 100,
                'forms' => [
                    Travel::TRAVEL_INFORMATION_FORM_KEY => true,
                ],
                'primary_contact_user_id' => $officer->id,
                'included_with_fee' => 'mailbook',
                'is_international' => false,
                'status' => 'draft',
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
        ->variant('Trip Fee - Cash - Need Airfare Request Form', static function (): PaymentReceipt {
            $user = User::withoutEvents(static function (): User {
                $user = User::factory()->make([
                    'first_name' => 'George',
                    'preferred_name' => null,
                    'last_name' => 'Burdell',
                    'gt_email' => 'george.burdell@gatech.edu',
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
                'fee_amount' => 100,
                'forms' => [
                    Travel::AIRFARE_REQUEST_FORM_KEY => true,
                ],
                'primary_contact_user_id' => $officer->id,
                'included_with_fee' => 'mailbook',
                'is_international' => false,
                'status' => 'draft',
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
        ->variant('Trip Fee - Cash - Need Both Forms', static function (): PaymentReceipt {
            $user = User::withoutEvents(static function (): User {
                $user = User::factory()->make([
                    'first_name' => 'George',
                    'preferred_name' => null,
                    'last_name' => 'Burdell',
                    'gt_email' => 'george.burdell@gatech.edu',
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
                'fee_amount' => 100,
                'forms' => [
                    Travel::TRAVEL_INFORMATION_FORM_KEY => true,
                    Travel::AIRFARE_REQUEST_FORM_KEY => true,
                ],
                'primary_contact_user_id' => $officer->id,
                'included_with_fee' => 'mailbook',
                'is_international' => false,
                'status' => 'draft',
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
        ->variant('Trip Fee - Cash - Don\'t Need Forms', static function (): PaymentReceipt {
            $user = User::withoutEvents(static function (): User {
                $user = User::factory()->make([
                    'first_name' => 'George',
                    'preferred_name' => null,
                    'last_name' => 'Burdell',
                    'gt_email' => 'george.burdell@gatech.edu',
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
                'fee_amount' => 100,
                'primary_contact_user_id' => $officer->id,
                'included_with_fee' => 'mailbook',
                'is_international' => false,
                'status' => 'draft',
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
});

Mailbook::category('Trip Assignments')->group(static function () use ($user): void {
    Mailbook::to($user)
        ->add(TravelAssignmentCreated::class)
        ->label('Trip Assignment Created')
        ->variant(
            'Need Travel Information Form And Emergency Contact No Payment',
            static function (): TravelAssignmentCreated {
                $user = User::withoutEvents(static function (): User {
                    $user = User::factory()->make([
                        'first_name' => 'George',
                        'preferred_name' => null,
                        'last_name' => 'Burdell',
                        'gt_email' => 'george.burdell@gatech.edu',
                        'primary_affiliation' => 'student',
                        'emergency_contact_name' => null,
                        'emergency_contact_phone' => null,
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
                    'fee_amount' => 0,
                    'forms' => [
                        Travel::TRAVEL_INFORMATION_FORM_KEY => true,
                    ],
                    'primary_contact_user_id' => $officer->id,
                    'included_with_fee' => 'mailbook',
                    'is_international' => false,
                    'status' => 'draft',
                ]));

                $assignment = TravelAssignment::withoutEvents(
                    static function () use ($user, $travel): TravelAssignment {
                        $assignment = TravelAssignment::factory()->make([
                            'user_id' => $user->id,
                            'travel_id' => $travel->id,
                        ]);
                        $assignment->save();

                        return $assignment;
                    }
                );

                return new TravelAssignmentCreated($assignment);
            }
        )
        ->variant('Need Travel Information Form No Payment', static function (): TravelAssignmentCreated {
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
                'fee_amount' => 0,
                'forms' => [
                    Travel::TRAVEL_INFORMATION_FORM_KEY => true,
                ],
                'primary_contact_user_id' => $officer->id,
                'included_with_fee' => 'mailbook',
                'is_international' => false,
                'status' => 'draft',
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
        ->variant('Need Travel Information Form', static function (): TravelAssignmentCreated {
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
                'forms' => [
                    Travel::TRAVEL_INFORMATION_FORM_KEY => true,
                ],
                'primary_contact_user_id' => $officer->id,
                'included_with_fee' => 'mailbook',
                'is_international' => false,
                'status' => 'draft',
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
        ->variant('Need Airfare Request Form', static function (): TravelAssignmentCreated {
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
                'forms' => [
                    Travel::AIRFARE_REQUEST_FORM_KEY => true,
                ],
                'primary_contact_user_id' => $officer->id,
                'included_with_fee' => 'mailbook',
                'is_international' => false,
                'status' => 'draft',
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
        ->variant('Need Both Forms', static function (): TravelAssignmentCreated {
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
                'forms' => [
                    Travel::TRAVEL_INFORMATION_FORM_KEY => true,
                    Travel::AIRFARE_REQUEST_FORM_KEY => true,
                ],
                'primary_contact_user_id' => $officer->id,
                'included_with_fee' => 'mailbook',
                'is_international' => false,
                'status' => 'draft',
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
                'return_date' => Carbon::now()->addDay(),
                'fee_amount' => 20,
                'forms' => [
                    Travel::TRAVEL_INFORMATION_FORM_KEY => true,
                ],
                'primary_contact_user_id' => $officer->id,
                'included_with_fee' => 'mailbook',
                'is_international' => false,
                'status' => 'draft',
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
                'return_date' => Carbon::now()->addDay(),
                'fee_amount' => 20,
                'primary_contact_user_id' => $officer->id,
                'included_with_fee' => 'mailbook',
                'is_international' => false,
                'status' => 'draft',
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
        ->variant('Need Payment - Future Trip', static function (): TravelAssignmentCreated {
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
                'return_date' => Carbon::now()->addDay(),
                'fee_amount' => 20,
                'primary_contact_user_id' => $officer->id,
                'included_with_fee' => 'mailbook',
                'is_international' => false,
                'status' => 'draft',
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
        ->variant('Need Payment - Past Trip', static function (): TravelAssignmentCreated {
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
                'return_date' => Carbon::now()->subDay(),
                'fee_amount' => 20,
                'primary_contact_user_id' => $officer->id,
                'included_with_fee' => 'mailbook',
                'is_international' => false,
                'status' => 'draft',
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
        ->label('Trip Assignment Reminder')
        ->variant('Need Travel Information Form', static function (): TravelAssignmentReminder {
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
                'forms' => [
                    Travel::TRAVEL_INFORMATION_FORM_KEY => true,
                ],
                'primary_contact_user_id' => $officer->id,
                'included_with_fee' => 'mailbook',
                'is_international' => false,
                'status' => 'draft',
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
        ->variant('Need Airfare Request Form', static function (): TravelAssignmentReminder {
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
                'forms' => [
                    Travel::AIRFARE_REQUEST_FORM_KEY => true,
                ],
                'primary_contact_user_id' => $officer->id,
                'included_with_fee' => 'mailbook',
                'is_international' => false,
                'status' => 'draft',
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
        ->variant('Need Both Forms', static function (): TravelAssignmentReminder {
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
                'forms' => [
                    Travel::TRAVEL_INFORMATION_FORM_KEY => true,
                    Travel::AIRFARE_REQUEST_FORM_KEY => true,
                ],
                'primary_contact_user_id' => $officer->id,
                'included_with_fee' => 'mailbook',
                'is_international' => false,
                'status' => 'draft',
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
                'forms' => [
                    Travel::TRAVEL_INFORMATION_FORM_KEY => true,
                ],
                'primary_contact_user_id' => $officer->id,
                'included_with_fee' => 'mailbook',
                'is_international' => false,
                'status' => 'draft',
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
                'forms' => [
                    Travel::TRAVEL_INFORMATION_FORM_KEY => true,
                ],
                'primary_contact_user_id' => $officer->id,
                'included_with_fee' => 'mailbook',
                'is_international' => false,
                'status' => 'draft',
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
                'forms' => [
                    Travel::TRAVEL_INFORMATION_FORM_KEY => true,
                ],
                'primary_contact_user_id' => $officer->id,
                'included_with_fee' => 'mailbook',
                'is_international' => false,
                'status' => 'draft',
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
                'primary_contact_user_id' => $officer->id,
                'included_with_fee' => 'mailbook',
                'is_international' => false,
                'status' => 'draft',
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
                'primary_contact_user_id' => $officer->id,
                'included_with_fee' => 'mailbook',
                'is_international' => false,
                'status' => 'draft',
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
                'primary_contact_user_id' => $officer->id,
                'included_with_fee' => 'mailbook',
                'is_international' => false,
                'status' => 'draft',
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
        ->label('Trip Forms Received')
        ->variant('Travel Information Form - Need Payment', static function (): DocuSignEnvelopeReceived {
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
                'forms' => [
                    Travel::TRAVEL_INFORMATION_FORM_KEY => true,
                ],
                'primary_contact_user_id' => $officer->id,
                'included_with_fee' => 'mailbook',
                'is_international' => false,
                'status' => 'draft',
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
        ->variant('Airfare Request Form - Need Payment', static function (): DocuSignEnvelopeReceived {
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
                'forms' => [
                    Travel::AIRFARE_REQUEST_FORM_KEY => true,
                ],
                'primary_contact_user_id' => $officer->id,
                'included_with_fee' => 'mailbook',
                'is_international' => false,
                'status' => 'draft',
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
        ->variant('Both Forms - Need Payment', static function (): DocuSignEnvelopeReceived {
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
                'forms' => [
                    Travel::TRAVEL_INFORMATION_FORM_KEY => true,
                    Travel::AIRFARE_REQUEST_FORM_KEY => true,
                ],
                'primary_contact_user_id' => $officer->id,
                'included_with_fee' => 'mailbook',
                'is_international' => false,
                'status' => 'draft',
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
                'forms' => [
                    Travel::TRAVEL_INFORMATION_FORM_KEY => true,
                    Travel::AIRFARE_REQUEST_FORM_KEY => true,
                ],
                'primary_contact_user_id' => $officer->id,
                'included_with_fee' => 'mailbook',
                'is_international' => false,
                'status' => 'draft',
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
                'forms' => [
                    Travel::TRAVEL_INFORMATION_FORM_KEY => true,
                    Travel::AIRFARE_REQUEST_FORM_KEY => true,
                ],
                'primary_contact_user_id' => $officer->id,
                'included_with_fee' => 'mailbook',
                'is_international' => false,
                'status' => 'draft',
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
                'forms' => [
                    Travel::TRAVEL_INFORMATION_FORM_KEY => true,
                    Travel::AIRFARE_REQUEST_FORM_KEY => true,
                ],
                'primary_contact_user_id' => $officer->id,
                'included_with_fee' => 'mailbook',
                'is_international' => false,
                'status' => 'draft',
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

    Mailbook::to(null)
        ->add(TravelMail::class)
        ->label('DocuSign Trip Forms')
        ->variant('Only Travel Information Form', static function (): TravelMail {
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
                'forms' => [
                    Travel::TRAVEL_INFORMATION_FORM_KEY => true,
                ],
                'primary_contact_user_id' => $officer->id,
                'included_with_fee' => 'mailbook',
                'is_international' => false,
                'status' => 'draft',
            ]));

            $assignment = TravelAssignment::withoutEvents(static function () use ($user, $travel): TravelAssignment {
                $assignment = TravelAssignment::factory()->make([
                    'user_id' => $user->id,
                    'travel_id' => $travel->id,
                ]);
                $assignment->save();

                return $assignment;
            });

            return new TravelMail($assignment);
        })
        ->variant('Only Airfare Request Form', static function (): TravelMail {
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
                'primary_contact_user_id' => $officer->id,
                'included_with_fee' => 'mailbook',
                'is_international' => false,
                'forms' => [
                    Travel::AIRFARE_REQUEST_FORM_KEY => true,
                ],
                'status' => 'draft',
            ]));

            $assignment = TravelAssignment::withoutEvents(static function () use ($user, $travel): TravelAssignment {
                $assignment = TravelAssignment::factory()->make([
                    'user_id' => $user->id,
                    'travel_id' => $travel->id,
                ]);
                $assignment->save();

                return $assignment;
            });

            return new TravelMail($assignment);
        })
        ->variant('Both Forms', static function (): TravelMail {
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
                'forms' => [
                    Travel::TRAVEL_INFORMATION_FORM_KEY => true,
                    Travel::AIRFARE_REQUEST_FORM_KEY => true,
                ],
                'primary_contact_user_id' => $officer->id,
                'included_with_fee' => 'mailbook',
                'is_international' => false,
                'status' => 'draft',
            ]));

            $assignment = TravelAssignment::withoutEvents(static function () use ($user, $travel): TravelAssignment {
                $assignment = TravelAssignment::factory()->make([
                    'user_id' => $user->id,
                    'travel_id' => $travel->id,
                ]);
                $assignment->save();

                return $assignment;
            });

            return new TravelMail($assignment);
        });
});

Mailbook::category('Trips')->group(static function () use ($user): void {
    Mailbook::to($user)
        ->add(AllTravelAssignmentsComplete::class)
        ->label('Trip Assignments Complete')
        ->variant('Forms and Payments - Past Trip', static function (): AllTravelAssignmentsComplete {
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
                'departure_date' => Carbon::now()->subDay(),
                'return_date' => '2022-02-21',
                'fee_amount' => 20,
                'forms' => [
                    Travel::TRAVEL_INFORMATION_FORM_KEY => true,
                ],
                'primary_contact_user_id' => $officer->id,
                'included_with_fee' => 'mailbook',
                'is_international' => false,
                'status' => 'draft',
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
        ->variant('Forms and Payments - Future Trip', static function (): AllTravelAssignmentsComplete {
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
                'departure_date' => Carbon::now()->addDay(),
                'return_date' => '2022-02-21',
                'fee_amount' => 20,
                'forms' => [
                    Travel::TRAVEL_INFORMATION_FORM_KEY => true,
                ],
                'primary_contact_user_id' => $officer->id,
                'included_with_fee' => 'mailbook',
                'is_international' => false,
                'status' => 'draft',
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
        ->variant('Forms Only - No Payment', static function (): AllTravelAssignmentsComplete {
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
                'fee_amount' => 0,
                'forms' => [
                    Travel::TRAVEL_INFORMATION_FORM_KEY => true,
                ],
                'primary_contact_user_id' => $officer->id,
                'included_with_fee' => 'mailbook',
                'is_international' => false,
                'status' => 'draft',
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
                'forms' => [
                    Travel::TRAVEL_INFORMATION_FORM_KEY => true,
                ],
                'primary_contact_user_id' => $officer->id,
                'included_with_fee' => 'mailbook',
                'is_international' => false,
                'status' => 'draft',
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
                'forms' => [
                    Travel::TRAVEL_INFORMATION_FORM_KEY => true,
                ],
                'primary_contact_user_id' => $officer->id,
                'included_with_fee' => 'mailbook',
                'is_international' => false,
                'status' => 'draft',
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
        ->variant('Payments Only - Need One Travel Information', static function (): AllTravelAssignmentsComplete {
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
                'forms' => [
                    Travel::TRAVEL_INFORMATION_FORM_KEY => true,
                ],
                'primary_contact_user_id' => $officer->id,
                'included_with_fee' => 'mailbook',
                'is_international' => false,
                'status' => 'draft',
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
        ->variant('Payments Only - Need One Airfare Request Form', static function (): AllTravelAssignmentsComplete {
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
                'forms' => [
                    Travel::AIRFARE_REQUEST_FORM_KEY => true,
                ],
                'primary_contact_user_id' => $officer->id,
                'included_with_fee' => 'mailbook',
                'is_international' => false,
                'status' => 'draft',
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
        ->variant('Payments Only - Need One Both Forms', static function (): AllTravelAssignmentsComplete {
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
                'forms' => [
                    Travel::TRAVEL_INFORMATION_FORM_KEY => true,
                    Travel::AIRFARE_REQUEST_FORM_KEY => true,
                ],
                'primary_contact_user_id' => $officer->id,
                'included_with_fee' => 'mailbook',
                'is_international' => false,
                'status' => 'draft',
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
                'forms' => [
                    Travel::TRAVEL_INFORMATION_FORM_KEY => true,
                ],
                'primary_contact_user_id' => $officer->id,
                'included_with_fee' => 'mailbook',
                'is_international' => false,
                'status' => 'draft',
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
        ->variant('Payments Only - Don\'t Need Forms - Past Trip', static function (): AllTravelAssignmentsComplete {
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
                'departure_date' => Carbon::now()->subDay(),
                'return_date' => '2022-02-21',
                'fee_amount' => 20,
                'primary_contact_user_id' => $officer->id,
                'included_with_fee' => 'mailbook',
                'is_international' => false,
                'status' => 'draft',
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
        ->variant('Payments Only - Don\'t Need Forms - Future Trip', static function (): AllTravelAssignmentsComplete {
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
                'departure_date' => Carbon::now()->addDay(),
                'return_date' => '2022-02-21',
                'fee_amount' => 20,
                'primary_contact_user_id' => $officer->id,
                'included_with_fee' => 'mailbook',
                'is_international' => false,
                'status' => 'draft',
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
});

Mailbook::category('Personal Access Tokens')->group(static function () use ($user): void {
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
});

Mailbook::category('Dues Packages')->group(static function () use ($user): void {
    Mailbook::to($user)
        ->add(PackageExpirationReminder::class)
        ->label('Dues Package Expiration Reminder')
        ->variant('One Package', static function () use ($user): PackageExpirationReminder {
            $user->givePermissionTo('update-dues-packages');

            DuesPackage::withoutEvents(static fn (): DuesPackage => DuesPackage::firstOrCreate([
                'name' => 'Spring 2023',
                'cost' => 55,
                'access_end' => Carbon::now()->addDays(3),
            ]));

            return new PackageExpirationReminder();
        })
        ->variant('Two Packages Same Day', static function () use ($user): PackageExpirationReminder {
            $user->givePermissionTo('update-dues-packages');

            DuesPackage::withoutEvents(static fn (): DuesPackage => DuesPackage::firstOrCreate([
                'name' => 'Spring 2023',
                'cost' => 55,
                'access_end' => Carbon::now()->addDays(3),
            ]));

            DuesPackage::withoutEvents(static fn (): DuesPackage => DuesPackage::firstOrCreate([
                'name' => 'Full Year (2022-2023)',
                'cost' => 100,
                'access_end' => Carbon::now()->addDays(3),
            ]));

            return new PackageExpirationReminder();
        })
        ->variant('Two Packages Different Day', static function () use ($user): PackageExpirationReminder {
            $user->givePermissionTo('update-dues-packages');

            DuesPackage::withoutEvents(static fn (): DuesPackage => DuesPackage::firstOrCreate([
                'name' => 'Spring 2023',
                'cost' => 55,
                'access_end' => Carbon::now()->addDays(3),
            ]));

            DuesPackage::withoutEvents(static fn (): DuesPackage => DuesPackage::firstOrCreate([
                'name' => 'Full Year (2022-2023)',
                'cost' => 100,
                'access_end' => Carbon::now()->addDays(4),
            ]));

            return new PackageExpirationReminder();
        });
});

Mailbook::category('Sponsors')->group(static function (): void {
    Mailbook::to(null)
        ->add(static function (): SponsorOneTimePassword {
            $sponsor = Sponsor::withoutEvents(static function (): Sponsor {
                $sponsor = new Sponsor();
                $sponsor->name = 'Sponsor Company';
                $sponsor->end_date = now()->addYear();
                $sponsor->save();

                return $sponsor;
            });

            $sponsorUser = SponsorUser::withoutEvents(static function () use ($sponsor): SponsorUser {
                $sponsorUser = new SponsorUser();
                $sponsorUser->email = 'person@sponsorcompany.com';
                $sponsorUser->sponsor_id = $sponsor->id;
                $sponsorUser->save();

                return $sponsorUser;
            });

            $otp = OneTimePassword::withoutEvents(static function () use ($sponsorUser): OneTimePassword {
                $otp = new OneTimePassword();
                $otp->authenticatable_type = $sponsorUser->getMorphClass();
                $otp->authenticatable_id = $sponsorUser->id;
                $otp->password = '123456';
                $otp->expires_at = now()->addMinutes(10);
                $otp->save();

                return $otp;
            });

            return new SponsorOneTimePassword($otp);
        })
        ->label('Sponsor One-Time Password');
});
