<?php

declare(strict_types=1);

// phpcs:disable Generic.Strings.UnnecessaryStringConcat.Found,Squiz.WhiteSpace.OperatorSpacing.SpacingAfter

namespace App\Nova\Actions;

use App\Attendance;
use App\Nova\RemoteAttendanceLink;
use App\RemoteAttendanceLink;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Lynndigital\SelectOrCustom\SelectOrCustom;

class CreateRemoteAttendanceLink extends Action
{
    /**
     * Indicates if this action is only available on the resource detail view.
     *
     * @var bool
     */
    public $onlyOnDetail = true;

    /**
     * Perform the action on the given models.
     *
     * @param \Illuminate\Support\Collection<\App\Team|\App\Event>  $models
     *
     * @return array<string,string>
     */
    public function handle(ActionFields $fields, Collection $models): array
    {
        // Four hours is longer than most meetings, but not too much longer than that.
        $expiration = Carbon::now()->addHours(4);

        // Only on detail, so it will only ever have one model.
        $attendable = $models->first();

        $link = new RemoteAttendanceLink();

        $link->attendable_type = get_class($attendable);
        $link->attendable_id = $attendable->id;
        $link->secret = hash('sha256', random_bytes(64));
        $link->expires_at = $expiration;
        $link->redirect_url = $fields->redirect_url;
        // If Purpose is other, set it to the Other Purpose value or null if that's empty. If the Purpose isn't other,
        // use that value. This deliberately allows empty values.
        $link->note = $fields->purpose;
        $link->save();
        $link->refresh(); // Update id field

        $user = Auth::user();
        $attExisting = Attendance::where('attendable_type', get_class($attendable))
            ->where('attendable_id', $attendable->id)
            ->where('gtid', $user->gtid)
            ->whereDate('created_at', date('Y-m-d'))->count();

        if (0 === $attExisting) {
            $att = new Attendance();
            $att->attendable_type = get_class($attendable);
            $att->attendable_id = $attendable->id;
            $att->gtid = $user->gtid;
            $att->source = 'secret-link-creation-'.$link->id;
            $att->recorded_by = $user->id;
            $att->save();
        }

        return Action::push('/resources/remote-attendance-links/'.$link->id);
    }

    /**
     * Get the fields available on the action.
     *
     * @return array<\Laravel\Nova\Fields\Field>
     */
    public function fields(): array
    {
        $notes = collect(RemoteAttendanceLink::$recommendedNotes)
            ->concat(['Other'])->mapWithKeys(static function (string $note): array {
                return $note => $note;
            });

        /* The following regex will match any of the following:
         * https://bluejeans.com/<digits, optional query string>
         * https://bluejeans.com/<digits>/<digits, optional query string>
         * https://gatech.bluejeans.com/<digits, optional query string>
         * https://gatech.bluejeans.com/<digits>/<digits, optional query string>
         * https://primetime.bluejeans.com/a2m/live-event/<alpha>
         * https://meet.google.com/<alpha and dashes>
         *
         * This keeps the redirects sane. Admins can manually create a RemoteAttendanceLink model with arbitrary URLs.
         */
        return [
            Text::make('Redirect URL')
                ->required(false)
                ->rules('nullable', 'url', 'regex:/^https:\/\/((gatech\.)?bluejeans\.com\/[0-9]+(\/[0-9]+)?|primetime'.
                    '\.bluejeans\.com\/a2m\/live-event\/[a-z]+|meet\.google\.com\/[-a-z]+)(\?[^@]*)?$/', 'max:1023')
                ->help('If you put a link to a BlueJeans or Google Meet meeting here, everyone who clicks the '.
                    'attendance link will be redirected to that meeting after their attendance is recorded. If '.
                    'you add a redirect URL, do not share that URL directly. Only Google Meet and '.
                    'BlueJeans calls are supported currently. Ask in #it-helpdesk for other redirect URLs.'),

            SelectOrCustom::make('Purpose')
                ->required(true)
                ->rules('required')
                ->options($notes),
        ];
    }
}
