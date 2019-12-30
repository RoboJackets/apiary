<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

/**
 * Maps a collection of models to an array of their IDs.
 *
 * @param \Illuminate\Support\Collection $collection A collection of models
 *
 * @return array<int> the models' IDs
 */
function idMap(\Illuminate\Support\Collection $collection): array
{
    return $collection->map(static function ($el): int {
        return $el->id;
    })->toArray();
}

$factory->define(App\RecruitingVisit::class, static function (Faker\Generator $faker): array {
    static $password;

    return [
        'recruiting_name' => $faker->name,
        'recruiting_email' => $faker->safeEmail,
        'visit_token' => $faker->asciify('********************'),
    ];
});

$factory->define(App\DuesPackage::class, static function (\Faker\Generator $faker): array {
    return [
        'name' => $faker->word,
        'eligible_for_shirt' => $faker->numberBetween(0, 1),
        'eligible_for_polo' => $faker->numberBetween(0, 1),
        'effective_start' => $faker->dateTimeBetween('-5 years', '-1 year'),
        'effective_end' => $faker->dateTimeBetween('-11 months', 'now'),
        'cost' => (string) $faker->randomFloat(2, 0, 1000),
    ];
});

$factory->define(App\DuesTransaction::class, static function (\Faker\Generator $faker): array {
    return [
        'swag_shirt_provided' => $faker->optional()->dateTime,
        'swag_shirt_providedBy' => null !== App\User::first() ? App\User::first()->id : null,
        'swag_polo_provided' => $faker->optional()->dateTime,
        'swag_polo_providedBy' => null !== App\User::first() ? App\User::first()->id : null,
        'dues_package_id' => $faker->randomElement(idMap(App\DuesPackage::all())),
        'user_id' => $faker->randomElement(idMap(App\User::all())),
    ];
});

$factory->define(App\User::class, static function (\Faker\Generator $faker): array {
    $lastName = $faker->lastName;
    $uid = $faker->bothify('?'.lcfirst($lastName).'##');

    return [
        'uid' => $uid,
        'gtid' => $faker->numerify('#########'),
        'slack_id' => null,
        'gt_email' => $uid.'@gatech.edu',
        'personal_email' => $faker->safeEmail,
        'first_name' => $faker->firstName,
        'middle_name' => $faker->optional()->lastName,
        'last_name' => $lastName,
        'preferred_name' => $faker->optional()->firstName,
        'phone' => $faker->numerify('##########'),
        'emergency_contact_name' => null,
        'emergency_contact_phone' => null,
        'join_semester' => null,
        'graduation_semester' => null,
        'shirt_size' => $faker->randomElement(['s', 'm', 'l', 'xl']),
        'polo_size' => $faker->randomElement(['s', 'm', 'l', 'xl']),
        'gender' => $faker->randomElement(['male', 'female', 'nonbinary', 'none']),
        'ethnicity' => $faker->randomElement(['white', 'asian', 'hispanic', 'black', 'native', 'islander', 'none']),
        'accept_safety_agreement' => $faker->optional()->dateTime,
    ];
});

$factory->define(App\Payment::class, static function (\Faker\Generator $faker): array {
    return [
        'payable_id' => $faker->numberBetween(0, 200),
        'payable_type' => $faker->randomElement([\App\DuesTransaction::class, \App\Event::class]),
        'amount' => (string) $faker->randomFloat(2, 0, 1000),
        'processing_fee' => (string) $faker->randomFloat(2, 0, 1000),
        'method' => 'square',
        'recorded_by' => $faker->randomElement(idMap(App\User::all())),
        'checkout_id' => null,
        'client_txn_id' => null,
        'server_txn_id' => null,
        'unique_id' => $faker->asciify('********************'),
        'notes' => 'Pending square payment',
    ];
});
