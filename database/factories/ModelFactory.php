<?php

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
function idMap($collection)
{
    return $collection->map(function ($el) {
        return $el->id;
    })->toArray();
}

$factory->define(App\FasetVisit::class, function (Faker\Generator $faker) {
    static $password;

    return [
        'faset_name' => $faker->name,
        'faset_email' => $faker->safeEmail,
        'visit_token' => $faker->asciify('********************'),
    ];
});

$factory->define(App\DuesPackage::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->word,
        'eligible_for_shirt' => $faker->numberBetween($min = 0, $max = 1),
        'eligible_for_polo' => $faker->numberBetween($min = 0, $max = 1),
        'effective_start' => $faker->dateTimeBetween($startDate = '-5 years', $endDate = '-1 year'),
        'effective_end' => $faker->dateTimeBetween($startDate = '-11 months', $endDate = 'now'),
        'cost' => (string) $faker->randomFloat($nbMaxDecimals = 2, $min = 0, $max = 1000),
    ];
});

$factory->define(App\DuesTransaction::class, function (Faker\Generator $faker) {
    return [
        'swag_shirt_provided' => $faker->optional()->dateTime,
        'swag_shirt_providedBy' => App\User::first() != null ? App\User::first()->id : null,
        'swag_polo_provided' => $faker->optional()->dateTime,
        'swag_polo_providedBy' => App\User::first() != null ? App\User::first()->id : null,
        'dues_package_id' => $faker->randomElement($array = idMap(App\DuesPackage::all())),
        'user_id' => $faker->randomElement($array = idMap(App\User::all())),
    ];
});

$factory->define(App\User::class, function (Faker\Generator $faker) {
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
        'shirt_size' => $faker->randomElement($array = ['s', 'm', 'l', 'xl']),
        'polo_size' => $faker->randomElement($array = ['s', 'm', 'l', 'xl']),
        'gender' => $faker->randomElement($array = ['male', 'female', 'nonbinary', 'none']),
        'ethnicity' => $faker->randomElement($array =
        ['white', 'asian', 'hispanic', 'black', 'native', 'islander', 'none']),
        'accept_safety_agreement' => $faker->optional()->dateTime,
    ];
});

$factory->define(App\Payment::class, function (Faker\Generator $faker) {
    return [
        'payable_id' => $faker->numberBetween($min = 0, $max = 200),
        'payable_type' => $faker->randomElement($array = ['App\DuesTransaction', 'App\Event']),
        'amount' => (string) $faker->randomFloat($nbMaxDecimals = 2, $min = 0, $max = 1000),
        'processing_fee' => (string) $faker->randomFloat($nbMaxDecimals = 2, $min = 0, $max = 1000),
        'method' => 'square',
        'recorded_by' => $faker->randomElement($array = idMap(App\User::all())),
        'checkout_id' => null,
        'client_txn_id' => null,
        'server_txn_id' => null,
        'unique_id' => $faker->asciify('********************'),
        'notes' => 'Pending square payment',
    ];
});
