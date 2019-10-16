<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\RecruitingVisit;
use App\Mail\DatabaseMailable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use App\Notifications\GeneralInterestNotification;
use App\Http\Requests\SendNotificationManualNotificationRequest;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:send-notifications']);
    }

    public function sendNotification(): JsonResponse
    {
        $hours = 0;
        RecruitingVisit::chunk(30, static function (Collection $chunk) use (&$hours): void {
            $when = Carbon::now()->addHours($hours);
            Notification::send($chunk, (new GeneralInterestNotification())->delay($when));
            $hours++;
        });

        return response()->json(['status' => 'success']);
    }

    public function sendNotificationManual(SendNotificationManualNotificationRequest $request): JsonResponse
    {
        $template_type = $request->input('template_type');
        $template_id = $request->input('template_id');

        $hours = 0;
        $found = [];
        $notfound = [];
        $emails = $request->input('emails');
        $chunks = array_chunk($emails, 30);
        foreach ($chunks as $chunk) {
            $when = Carbon::now()->addHours($hours);
            if ('recruiting' === $template_type) {
                foreach ($chunk as $address) {
                    $visit = RecruitingVisit::where('recruiting_email', $address)->first();
                    if (isset($visit->id)) {
                        Notification::send($visit, (new GeneralInterestNotification())->delay($when));
                        $found[] = $visit->recruiting_email;
                    } else {
                        $notfound[] = $address;
                    }
                }
                $hours++;
            } elseif ('database' === $template_type) {
                foreach ($chunk as $address) {
                    Mail::to($address)->queue(new DatabaseMailable(intval($template_id), null));
                    $found[] = $address;
                }
                $hours++;
            } else {
                return response()->json(['status' => 'error', 'error' => 'Invalid template type']);
            }
        }

        return response()->json([
            'status' => 'success',
            'found' => ['count' => count($found), 'emails' => $found],
            'notfound' => ['count' => count($notfound), 'emails' => $notfound],
        ]);
    }
}
