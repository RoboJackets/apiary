<?php

declare(strict_types=1);

// phpcs:disable Generic.Strings.UnnecessaryStringConcat.Found,Squiz.WhiteSpace.OperatorSpacing.SpacingAfter,SlevomatCodingStandard.Functions.RequireMultiLineCall.RequiredMultiLineCall

namespace App\Nova\Actions;

use App\Models\Attendance;
use App\Models\RemoteAttendanceLink;
use App\Nova\RemoteAttendanceLink as NovaRemoteAttendanceLink;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
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
     * @param  \Illuminate\Support\Collection<int,\App\Models\Team|\App\Models\Event>  $models
     * @return array<string,array<string,string>>
     *
     * @phan-suppress PhanTypeMismatchArgument
     */
    public function handle(ActionFields $fields, Collection $models): array
    {
        // Four hours is longer than most meetings, but not too much longer than that.
        $expiration = Carbon::now()->addHours(4);

        // Only on detail, so it will only ever have one model.
        $attendable = $models->first();

        $link = new RemoteAttendanceLink();

        $link->attendable_type = $attendable->getMorphClass();
        $link->attendable_id = $attendable->id;
        $link->secret = bin2hex(openssl_random_pseudo_bytes(32));
        $link->expires_at = $expiration;
        if (null !== $fields->redirect_url) {
            $link->redirect_url = RemoteAttendanceLink::normalizeRedirectUrl($fields->redirect_url);
        }
        $link->note = $fields->purpose;
        $link->save();
        $link->refresh(); // Update id field

        $user = Auth::user();
        $attExists = Attendance::where('attendable_type', $attendable->getMorphClass())
            ->where('attendable_id', $attendable->id)
            ->where('gtid', $user->gtid)
            ->whereDate('created_at', date('Y-m-d'))->exists();

        if (! $attExists) {
            $att = new Attendance();
            $att->attendable_type = $attendable->getMorphClass();
            $att->attendable_id = $attendable->id;
            $att->gtid = $user->gtid;
            $att->source = 'remote-attendance-link-creation';
            $att->recorded_by = $user->id;
            $att->remote_attendance_link_id = $link->id;
            $att->save();
        }

        return Action::visit('/resources/remote-attendance-links/'.$link->id);
    }

    /**
     * Get the fields available on the action.
     *
     * @return array<\Laravel\Nova\Fields\Field>
     */
    public function fields(NovaRequest $request): array
    {
        $notes = collect(NovaRemoteAttendanceLink::$recommendedNotes)
            ->mapWithKeys(static function (string $note): array {
                return [$note => $note];
            });

        return [
            Text::make('Redirect URL')
                ->required(false)
                ->rules('nullable', 'url', 'regex:'.RemoteAttendanceLink::$redirectRegex, 'max:1023')
                ->help('If you put a link to a Google Meet, BlueJeans, or Microsoft Teams meeting here, everyone who '.
                    'clicks the attendance link will be redirected to that meeting after their attendance is recorded.'.
                    ' If you add a redirect URL, do not share that URL directly. Only Google Meet, BlueJeans, and '.
                    'Microsoft Teams calls are supported currently. Ask in #it-helpdesk for other redirect URLs.'),

            SelectOrCustom::make('Purpose')
                ->required(true)
                ->rules('required')
                ->options($notes->toArray()),
        ];
    }
}
