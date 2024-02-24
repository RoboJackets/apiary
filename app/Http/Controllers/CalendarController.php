<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Travel;
use Eluceo\iCal\Domain\Entity\Calendar;
use Eluceo\iCal\Domain\Entity\Event as CalendarEvent;
use Eluceo\iCal\Domain\ValueObject\Date;
use Eluceo\iCal\Domain\ValueObject\DateTime;
use Eluceo\iCal\Domain\ValueObject\EmailAddress;
use Eluceo\iCal\Domain\ValueObject\Location;
use Eluceo\iCal\Domain\ValueObject\MultiDay;
use Eluceo\iCal\Domain\ValueObject\Organizer;
use Eluceo\iCal\Domain\ValueObject\TimeSpan;
use Eluceo\iCal\Domain\ValueObject\UniqueIdentifier;
use Eluceo\iCal\Domain\ValueObject\Uri;
use Eluceo\iCal\Presentation\Factory\CalendarFactory;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $events = Event::all()->map(static fn (Event $event): CalendarEvent => (new CalendarEvent(
            new UniqueIdentifier('event-'.$event->id.'@'.$request->getHost())
        ))
            ->setSummary($event->name)
            ->setUrl(new Uri(
                route(
                    'nova.pages.detail',
                    [
                        'resource' => \App\Nova\Event::uriKey(),
                        'resourceId' => $event->id,
                    ]
                )
            ))
            ->setOccurrence(new TimeSpan(
                begin: new DateTime($event->start_time, applyTimeZone: true),
                end: new DateTime($event->end_time, applyTimeZone: true)
            ))
            ->setLocation($event->location === null ? null : new Location($event->location))
            ->setOrganizer(
                new Organizer(
                    new EmailAddress($event->organizer->gt_email),
                    $event->organizer->name
                )
            ));

        $trips = Travel::all()->map(static fn (Travel $trip): CalendarEvent => (new CalendarEvent(
            new UniqueIdentifier('trip-'.$trip->id.'@'.$request->getHost())
        ))
            ->setSummary($trip->name)
            ->setUrl(new Uri(
                route(
                    'nova.pages.detail',
                    [
                        'resource' => \App\Nova\Travel::uriKey(),
                        'resourceId' => $trip->id,
                    ]
                )
            ))
            ->setOccurrence(new MultiDay(
                firstDay: new Date($trip->departure_date),
                lastDay: new Date($trip->return_date)
            ))
            ->setLocation(new Location($trip->destination))
            ->setOrganizer(
                new Organizer(
                    new EmailAddress($trip->primaryContact->gt_email),
                    $trip->primaryContact->name
                )
            ));

        return response(
            content: (new CalendarFactory())->createCalendar(new Calendar([...$events, ...$trips]))->__toString(),
            headers: ['Content-Type' => 'text/calendar; charset=utf-8']
        );
    }
}
