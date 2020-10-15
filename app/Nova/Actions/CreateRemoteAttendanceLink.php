<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\RemoteAttendanceLink;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;

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

        $link = new RemoteAttendanceLink;

        $link->attendable_type = get_class($attendable);
        $link->attendable_id = $attendable->id;
        $link->secret = hash('sha256', random_bytes(64));
        $link->expires_at = $expiration;
        $link->redirect_url = $fields->redirect_url;
        // If Purpose is other, set it to the Other Purpose value or null if that's empty. If the Purpose isn't other,
        // use that value. This deliberately allows empty values.
        $link->note = 'Other' === $fields->purpose ?
            (strlen($fields->other_purpose) > 0 ? $fields->other_purpose : null) : $fields->purpose;
        $team->save();

        return Action::message('A new link has been generated!');
    }

    /**
     * Get the fields available on the action.
     *
     * @return array<\Laravel\Nova\Fields\Field>
     */
    public function fields(): array
    {
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
                    'you add a redirect URL, do not share the meeting link directly. Only Google Meet and '.
                    'BlueJeans calls are supported currently. Contact an administrator for other redirect URLs.'),

            Select::make('Purpose')
                ->options([
                    'Electrical' => 'Electrical',
                    'Mechanical' => 'Mechanical',
                    'Software' => 'Software',
                    'Firmware' => 'Firmware',
                    'Mechatronics' => 'Mechatronics',
                    'Whole Team' => 'Whole Team',
                    'Other' => 'Other',
                ]),

            Text::make('Other Purpose')
                ->required(false)
                ->rules('nullable', 'max:255')
                ->help('Only fill this in if you picked "Other" above.'),
        ];
    }
}
