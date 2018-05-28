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
                'description' => "", 'slug' => "igvc"],
            ['name' => 'BattleBots', 'hidden' => false, 'attendable' => true, 'founding_year' => 1999,
                'description' => "", 'slug' => "battlebots"],
            ['name' => 'Outreach', 'hidden' => false, 'attendable' => true, 'founding_year' => 2001,
                'description' => "", 'slug' => "outreach"],
            ['name' => 'RoboCup', 'hidden' => false, 'attendable' => true, 'founding_year' => 2007,
                'description' => "", 'slug' => "robocup"],
            ['name' => 'RoboRacing', 'hidden' => false, 'attendable' => true, 'founding_year' => 2013,
                'description' => "", 'slug' => "roboracing"],
            ['name' => 'Core', 'hidden' => true, 'attendable' => true, 'founding_year' => 1969,
                'description' => "", 'slug' => "core"]
        ]);
    }
}
