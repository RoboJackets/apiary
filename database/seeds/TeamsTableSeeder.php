<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

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
            ['name' => 'IGVC', 'visible' => true, 'attendable' => true,
                'description' => 'IGVC', 'slug' => 'igvc', 'self_serviceable' => true ],
            ['name' => 'BattleBots', 'visible' => true, 'attendable' => true,
                'description' => 'BattleBots', 'slug' => 'battlebots', 'self_serviceable' => true ],
            ['name' => 'Outreach', 'visible' => true, 'attendable' => true,
                'description' => 'Outreach', 'slug' => 'outreach', 'self_serviceable' => true ],
            ['name' => 'RoboCup', 'visible' => true, 'attendable' => true,
                'description' => 'RoboCup', 'slug' => 'robocup', 'self_serviceable' => true ],
            ['name' => 'RoboRacing', 'visible' => true, 'attendable' => true,
                'description' => 'RoboRacing', 'slug' => 'roboracing', 'self_serviceable' => true ],
            ['name' => 'Core', 'visible' => true, 'attendable' => true,
                'description' => 'Core', 'slug' => 'core', 'self_serviceable' => false ],
        ]);
    }
}
