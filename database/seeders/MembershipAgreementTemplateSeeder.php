<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\MembershipAgreementTemplate;
use Illuminate\Database\Seeder;

class MembershipAgreementTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        MembershipAgreementTemplate::factory()->count(1)->create();
    }
}
