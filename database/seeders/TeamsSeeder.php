<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TeamsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('teams')->insert([
            ['name' => 'IGVC', 'visible' => true, 'attendable' => true, 'description' => 'IGVC',
                'self_serviceable' => true, 'visible_on_kiosk' => true,
            ],
            ['name' => 'BattleBots', 'visible' => true, 'attendable' => true, 'description' => 'BattleBots',
                'self_serviceable' => true, 'visible_on_kiosk' => true,
            ],
            ['name' => 'Outreach', 'visible' => true, 'attendable' => true, 'description' => 'Outreach',
                'self_serviceable' => true, 'visible_on_kiosk' => true,
            ],
            ['name' => 'RoboCup', 'visible' => true, 'attendable' => true, 'description' => 'RoboCup',
                'self_serviceable' => true, 'visible_on_kiosk' => true,
            ],
            ['name' => 'RoboRacing', 'visible' => true, 'attendable' => true, 'description' => 'RoboRacing',
                'self_serviceable' => true, 'visible_on_kiosk' => true,
            ],
            ['name' => 'Core', 'visible' => true, 'attendable' => true, 'description' => 'Core',
                'self_serviceable' => false, 'visible_on_kiosk' => true,
            ],
        ]);
    }
}
