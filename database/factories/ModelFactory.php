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

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\FasetVisit::class, function (Faker\Generator $faker) {
    static $password;

    return [
        'faset_name' => $faker->name,
        'faset_email' => $faker->safeEmail,
        'visit_token' => $faker->asciify('********************'),
    ];
});

//Cannot have use factory for DuesTransaction, since it requires a linking DuesPackage
$factory->define(App\DuesPackage::class, function (Faker\Generator $faker) {
    return [
        'id' => $faker->numberBetween($min = 0, $max = 200),
        'name' => $faker->word,
        'eligible_for_shirt' => $faker->numberBetween($min = 0, $max = 1),
        'eligible_for_polo' => $faker->numberBetween($min = 0, $max = 1),
        'effective_start' => $faker->dateTime(),
        'effective_end' => $faker->dateTime(),
        'cost' => (string) $faker->randomFloat($nbMaxDecimals = 2, $min = 0, $max = 1000),
    ];
});

$factory->define(App\User::class, function (Faker\Generator $faker) {
    $lastName = $faker->lastName;
    $uid = $faker-> $faker->bothify('?'.lcfirst($lastname).'##');
    return [
        'id' => $faker->numberBetween($min = 0, $max = 200),
        'uid' => $uid,
        'gtid' => $faker->numerify('#########'),
        'slack_id' => null,
        'gt_email' => $uid.'@gatech.edu',
        'personal_email' => $faker->safeEmail,
        'first_name' => $faker->firstName($gender = null|'male'|'female'),
        'middle_name' => $faker->optional()->lastName,
        'last_name' => $lastName,
        'preferred_name' => null,
        'phone' => $faker->numerify('##########'),
        'emergency_contact_name' => null,
        'emergency_contact_phone' => null,
        'join_semester' => null,
        'graduation_semester' => null,
        'shirt_size' => $faker->randomElement($array = array('s','m','l', 'xl')),
        'polo_size' => $faker->randomElement($array = array('s','m','l', 'xl')),
        'gender' => $faker->randomElement($array = array('male', 'female')),
        'ethnicity' => $faker->randomElement($array = array()),
    ];
});