<?php

declare(strict_types=1);

use Illuminate\Database\Seeder;

class TeamsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('teams')->insert([
            ['name' => 'IGVC', 'visible' => true, 'attendable' => true,
                'description' => 'IGVC', 'slug' => 'igvc', 'self_serviceable' => true, 'visible_on_kiosk' => true,
            ],
            ['name' => 'BattleBots', 'visible' => true, 'attendable' => true,
                'description' => 'BattleBots', 'slug' => 'battlebots', 'self_serviceable' => true, 'visible_on_kiosk' => true,
            ],
            ['name' => 'Outreach', 'visible' => true, 'attendable' => true,
                'description' => 'Outreach', 'slug' => 'outreach', 'self_serviceable' => true, 'visible_on_kiosk' => true,
            ],
            ['name' => 'RoboCup', 'visible' => true, 'attendable' => true,
                'description' => 'RoboCup', 'slug' => 'robocup', 'self_serviceable' => true, 'visible_on_kiosk' => true,
            ],
            ['name' => 'RoboRacing', 'visible' => true, 'attendable' => true,
                'description' => 'RoboRacing', 'slug' => 'roboracing', 'self_serviceable' => true, 'visible_on_kiosk' => true,
            ],
            ['name' => 'Core', 'visible' => true, 'attendable' => true,
                'description' => 'Core', 'slug' => 'core', 'self_serviceable' => false, 'visible_on_kiosk' => true,
            ],
        ]);
    }
}
