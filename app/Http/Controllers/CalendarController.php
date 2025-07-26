<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.PHP.UselessParentheses.UselessParentheses

namespace App\Http\Controllers;

use App\Models\DuesPackage;
use App\Models\Event;
use App\Models\Travel;
use App\Models\TravelAssignment;
use Carbon\CarbonImmutable;
use Eluceo\iCal\Domain\Entity\Calendar;
use Eluceo\iCal\Domain\Entity\Event as CalendarEvent;
use Eluceo\iCal\Domain\ValueObject\Date;
use Eluceo\iCal\Domain\ValueObject\DateTime;
use Eluceo\iCal\Domain\ValueObject\EmailAddress;
use Eluceo\iCal\Domain\ValueObject\Location;
use Eluceo\iCal\Domain\ValueObject\MultiDay;
use Eluceo\iCal\Domain\ValueObject\Organizer;
use Eluceo\iCal\Domain\ValueObject\SingleDay;
use Eluceo\iCal\Domain\ValueObject\TimeSpan;
use Eluceo\iCal\Domain\ValueObject\UniqueIdentifier;
use Eluceo\iCal\Domain\ValueObject\Uri;
use Eluceo\iCal\Presentation\Factory\CalendarFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class CalendarController
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $events = Event::with('organizer')
            ->whereNotNull('start_time')
            ->whereNotNull('end_time')
            ->get()
            ->map(
                static fn (Event $event): CalendarEvent => (new CalendarEvent(
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
                    )
            );

        $trips = Travel::with('primaryContact')
            ->get()
            ->map(
                static fn (Travel $trip): CalendarEvent => (new CalendarEvent(
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
                    )
            );

        $assignments = TravelAssignment::whereNotNull('matrix_itinerary')
            ->get()
            ->map(
                static fn (TravelAssignment $assignment): Collection => collect(
                    // @phan-suppress-next-line PhanTypeArraySuspiciousNullable
                    $assignment->matrix_itinerary['itinerary']['slices']
                )->map(
                    static fn (array $slice): Collection => collect($slice['segments'])->map(
                        static fn (array $segment): CalendarEvent => (new CalendarEvent(
                            new UniqueIdentifier(
                                'segment-'.$segment['carrier']['code'].$segment['flight']['number'].'-'.
                                $segment['origin']['code'].'-'.$segment['destination']['code'].'-'.
                                substr($segment['departure'], 0, 10).'@'.$request->getHost()
                            )
                        ))
                            ->setSummary(
                                $segment['carrier']['code'].$segment['flight']['number'].' '.
                                $segment['origin']['code'].' to '.$segment['destination']['code']
                            )
                            ->setUrl(new Uri(
                                'https://www.flightaware.com/search/'.$segment['carrier']['code'].
                                $segment['flight']['number']
                            ))
                            ->setOccurrence(new TimeSpan(
                                begin: new DateTime(CarbonImmutable::parse($segment['departure'])->utc(), applyTimeZone: true),
                                end: new DateTime(CarbonImmutable::parse($segment['arrival'])->utc(), applyTimeZone: true)
                            ))
                            ->setLocation(
                                new Location(
                                    $segment['origin']['city']['name'].' ('.$segment['origin']['code'].')'
                                )
                            )
                            ->setDescription(
                                (
                                    (
                                        array_key_exists('ext', $segment) &&
                                        array_key_exists('operationalDisclosure', $segment['ext'])
                                    ) ? $segment['ext']['operationalDisclosure']."\n\n" : '').
                                'https://www.flightaware.com/search/'.$segment['carrier']['code'].
                                $segment['flight']['number']
                            )
                    )
                )
            )
            ->flatten()
            ->unique(static fn (CalendarEvent $event): string => $event->getUniqueIdentifier()->__toString());

        $packages = DuesPackage::all();

        $membershipEndDates = $packages->map(
            static fn (DuesPackage $package): CalendarEvent => (new CalendarEvent(
                new UniqueIdentifier('package-'.$package->id.'-membership@'.$request->getHost())
            ))
                ->setSummary($package->name.' Membership Ends')
                ->setUrl(new Uri(
                    route(
                        'nova.pages.detail',
                        [
                            'resource' => \App\Nova\DuesPackage::uriKey(),
                            'resourceId' => $package->id,
                        ]
                    )
                ))
                ->setOccurrence(new SingleDay(new Date($package->effective_end)))
        );

        $accessEndDates = $packages->map(
            static fn (DuesPackage $package): CalendarEvent => (new CalendarEvent(
                new UniqueIdentifier('package-'.$package->id.'-access@'.$request->getHost())
            ))
                ->setSummary($package->name.' Access Ends')
                ->setUrl(new Uri(
                    route(
                        'nova.pages.detail',
                        [
                            'resource' => \App\Nova\DuesPackage::uriKey(),
                            'resourceId' => $package->id,
                        ]
                    )
                ))
                ->setOccurrence(new SingleDay(new Date($package->access_end)))
        );

        return response(
            content: (new CalendarFactory())->createCalendar(new Calendar([
                ...$events,
                ...$trips,
                ...$assignments,
                ...$membershipEndDates,
                ...$accessEndDates,
            ]))->__toString(),
            headers: ['Content-Type' => 'text/calendar; charset=utf-8']
        );
    }
}
