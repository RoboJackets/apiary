<?php

use Illuminate\Database\Seeder;

class TeamsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('teams')->insert([
            ['name' => 'IGVC', 'hidden' => false, 'attendable' => true, 'founding_year' => 2004,
                'long_description' => "", 'short_description' => ''],
            ['name' => 'BattleBots', 'hidden' => false, 'attendable' => true, 'founding_year' => 1999,
                'long_description' => "", 'short_description' => ''],
            ['name' => 'Outreach', 'hidden' => false, 'attendable' => true, 'founding_year' => 2001,
                'long_description' => "", 'short_description' => ''],
            ['name' => 'RoboCup', 'hidden' => false, 'attendable' => true, 'founding_year' => 2007,
                'long_description' => "", 'short_description' => ''],
            ['name' => 'RoboRacing', 'hidden' => false, 'attendable' => true, 'founding_year' => 2013,
                'long_description' => "", 'short_description' => ''],
            ['name' => 'Core', 'hidden' => true, 'attendable' => true, 'founding_year' => 1969,
                'long_description' => "", 'short_description' => '']
        ]);
    }
}
