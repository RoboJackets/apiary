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
            ['name' => 'IGVC', 'visible' => true, 'attendable' => true, 'founding_year' => 2004,
                'description' => '', 'slug' => 'igvc', 'self_serviceable' => true, ],
            ['name' => 'BattleBots', 'visible' => true, 'attendable' => true, 'founding_year' => 1999,
                'description' => '', 'slug' => 'battlebots', 'self_serviceable' => true, ],
            ['name' => 'Outreach', 'visible' => true, 'attendable' => true, 'founding_year' => 2001,
                'description' => '', 'slug' => 'outreach', 'self_serviceable' => true, ],
            ['name' => 'RoboCup', 'visible' => true, 'attendable' => true, 'founding_year' => 2007,
                'description' => '', 'slug' => 'robocup', 'self_serviceable' => true, ],
            ['name' => 'RoboRacing', 'visible' => true, 'attendable' => true, 'founding_year' => 2013,
                'description' => '', 'slug' => 'roboracing', 'self_serviceable' => true, ],
            ['name' => 'Core', 'hidden' => true, 'attendable' => true, 'founding_year' => 1969,
                'description' => '', 'slug' => 'core', 'self_serviceable' => false, ],
        ]);
    }
}
