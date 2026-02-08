<?php

declare(strict_types=1);

use App\Models\Attendance;
use App\Models\DuesPackage;
use App\Models\DuesTransaction;
use App\Models\Event;
use App\Models\Major;
use App\Models\Payment;
use App\Models\Rsvp;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $classMap = [
            'App\Attendance' => Attendance::class,
            'App\DuesPackage' => DuesPackage::class,
            'App\DuesTransaction' => DuesTransaction::class,
            'App\Event' => Event::class,
            'App\Major' => Major::class,
            'App\Payment' => Payment::class,
            'App\Rsvp' => Rsvp::class,
            'App\Team' => Team::class,
            'App\User' => User::class,
        ];

        $morphColumns = ['actionable_type', 'target_type', 'model_type'];

        foreach ($morphColumns as $column) {
            foreach ($classMap as $old => $new) {
                DB::table('action_events')
                    ->where($column, $old)
                    ->update([$column => $new]);
            }
        }
    }
};
