<?php
namespace Database\Factories\RecruitingVisit;
use Illuminate\Database\Eloquent\Factories\Factory;


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

class ModelFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \App\RecruitingVisit::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        static $password;

        return [
            'recruiting_name' => $this->faker->name,
            'recruiting_email' => $this->faker->safeEmail,
            'visit_token' => $this->faker->asciify('********************'),
        ];
    }

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'eligible_for_shirt' => $this->faker->numberBetween(0, 1),
            'eligible_for_polo' => $this->faker->numberBetween(0, 1),
            'effective_start' => $this->faker->dateTimeBetween('-5 years', '-1 year'),
            'effective_end' => $this->faker->dateTimeBetween('-11 months', 'now'),
            'cost' => (string) $this->faker->randomFloat(2, 0, 1000),
        ];
    }

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'swag_shirt_provided' => $this->faker->optional()->dateTime,
            'swag_shirt_providedBy' => null !== App\User::first() ? App\User::first()->id : null,
            'swag_polo_provided' => $this->faker->optional()->dateTime,
            'swag_polo_providedBy' => null !== App\User::first() ? App\User::first()->id : null,
            'dues_package_id' => $this->faker->randomElement(idMap(App\DuesPackage::all())),
            'user_id' => $this->faker->randomElement(idMap(App\User::all())),
        ];
    }

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $lastName = $this->faker->lastName;
        $uid = $this->faker->bothify('?'.lcfirst($lastName).'##');

        return [
            'uid' => $uid,
            'gtid' => $this->faker->numerify('#########'),
            'slack_id' => null,
            'gt_email' => $uid.'@gatech.edu',
            'personal_email' => $this->faker->safeEmail,
            'first_name' => $this->faker->firstName,
            'middle_name' => $this->faker->optional()->lastName,
            'last_name' => $lastName,
            'preferred_name' => $this->faker->optional()->firstName,
            'phone' => $this->faker->numerify('##########'),
            'emergency_contact_name' => null,
            'emergency_contact_phone' => null,
            'join_semester' => null,
            'graduation_semester' => null,
            'shirt_size' => $this->faker->randomElement(['s', 'm', 'l', 'xl']),
            'polo_size' => $this->faker->randomElement(['s', 'm', 'l', 'xl']),
            'gender' => $this->faker->randomElement(['male', 'female', 'nonbinary', 'none']),
            'ethnicity' => $this->faker->randomElement(['white', 'asian', 'hispanic', 'black', 'native', 'islander', 'none']),
            'accept_safety_agreement' => $this->faker->optional()->dateTime,
        ];
    }

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'payable_id' => $this->faker->numberBetween(0, 200),
            'payable_type' => $this->faker->randomElement([\App\DuesTransaction::class, \App\Event::class]),
            'amount' => (string) $this->faker->randomFloat(2, 0, 1000),
            'processing_fee' => (string) $this->faker->randomFloat(2, 0, 1000),
            'method' => 'square',
            'recorded_by' => $this->faker->randomElement(idMap(App\User::all())),
            'checkout_id' => null,
            'client_txn_id' => null,
            'server_txn_id' => null,
            'unique_id' => $this->faker->asciify('********************'),
            'notes' => 'Pending square payment',
        ];
    }
}
