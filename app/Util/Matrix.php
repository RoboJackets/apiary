<?php

declare(strict_types=1);

namespace App\Util;

use App\Models\Airport;
use Carbon\CarbonImmutable;
use Exception;

class Matrix
{
    /**
     * Returns the highest possible display price for a single passenger on this itinerary.
     */
    public static function getHighestDisplayPrice(string|array|null $itinerary): ?float
    {
        if ($itinerary === null) {
            return null;
        }

        if (is_string($itinerary)) {
            $itinerary = json_decode($itinerary, true);
        }

        if (
            ! is_array($itinerary) ||
            ! array_key_exists('pricings', $itinerary) ||
            ! is_array($itinerary['pricings'])
        ) {
            return null;
        }

        $highestDisplayPrice = null;

        foreach ($itinerary['pricings'] as $pricing) {
            if (
                ! is_array($pricing) ||
                ! array_key_exists('displayPrice', $pricing) ||
                ! is_string($pricing['displayPrice']) ||
                ! str_starts_with($pricing['displayPrice'], 'USD')
            ) {
                return null;
            }

            $thisDisplayPrice = floatval(substr($pricing['displayPrice'], 3));

            if ($highestDisplayPrice === null || $thisDisplayPrice > $highestDisplayPrice) {
                $highestDisplayPrice = $thisDisplayPrice;
            }
        }

        return $highestDisplayPrice;
    }

    /**
     * Returns the earliest departure time on this itinerary.
     *
     * @phan-suppress PhanTypeMismatchReturn
     */
    public static function getDepartureDateTime(array $decoded_itinerary): CarbonImmutable
    {
        return collect($decoded_itinerary['itinerary']['slices'])
            ->reduce(static function (?CarbonImmutable $carry, array $slice): CarbonImmutable {
                $earliest_segment = collect($slice['segments'])
                    ->reduce(static function (?CarbonImmutable $carry, array $segment): CarbonImmutable {
                        $earliest_leg = collect($segment['legs'])
                            ->reduce(static function (?CarbonImmutable $carry, array $leg): CarbonImmutable {
                                $departure = CarbonImmutable::parse($leg['departure']);

                                if ($carry === null || $departure->isBefore($carry)) {
                                    return $departure;
                                }

                                return $carry;
                            });

                        if ($carry === null || $earliest_leg->isBefore($carry)) {
                            return $earliest_leg;
                        }

                        return $carry;
                    });

                if ($carry === null || $earliest_segment->isBefore($carry)) {
                    return $earliest_segment;
                }

                return $carry;
            });
    }

    /**
     * Returns the latest departure time on this itinerary, if there are exactly two slices.
     *
     * @phan-suppress PhanTypeArraySuspicious
     */
    public static function getReturnDateTime(array $decoded_itinerary): ?CarbonImmutable
    {
        if (self::getSliceCount($decoded_itinerary) !== 2) {
            return null;
        }

        $latest_slice = collect($decoded_itinerary['itinerary']['slices'])
            ->sort(static function (array $first_slice, array $second_slice): int {
                $first_departure_time = CarbonImmutable::parse($first_slice['departure']);
                $second_departure_time = CarbonImmutable::parse($second_slice['departure']);

                if ($first_departure_time->equalTo($second_departure_time)) {
                    throw new Exception('Slices have equal departure time');
                }

                if ($first_departure_time->isBefore($second_departure_time)) {
                    return 1;
                }

                return -1;
            })
            ->first();

        return CarbonImmutable::parse($latest_slice['departure']);
    }

    public static function getDepartureFlightNumber(array $decoded_itinerary): ?string
    {
        if (
            self::getSliceCount($decoded_itinerary) > 2 ||
            count($decoded_itinerary['itinerary']['slices'][0]['segments']) > 1
        ) {
            return null;
        }

        $segment = $decoded_itinerary['itinerary']['slices'][0]['segments'][0];

        return $segment['carrier']['shortName'].' '.$segment['flight']['number'];
    }

    public static function getReturnFlightNumber(array $decoded_itinerary): ?string
    {
        if (
            self::getSliceCount($decoded_itinerary) !== 2 ||
            count($decoded_itinerary['itinerary']['slices'][1]['segments']) > 1
        ) {
            return null;
        }

        $segment = $decoded_itinerary['itinerary']['slices'][1]['segments'][0];

        return $segment['carrier']['shortName'].' '.$segment['flight']['number'];
    }

    public static function getSliceCount(array $decoded_itinerary): int
    {
        return count($decoded_itinerary['itinerary']['slices']);
    }

    /**
     * Return the itinerary origin and destination as a string for the airfare request form.
     *
     * @phan-suppress PhanTypeArraySuspicious
     */
    public static function getOriginDestinationString(array $decoded_itinerary): string
    {
        if (self::getSliceCount($decoded_itinerary) > 2) {
            return 'See attached itinerary';
        }

        $earliest_slice = collect($decoded_itinerary['itinerary']['slices'])
            ->sort(static function (array $first_slice, array $second_slice): int {
                $first_departure_time = CarbonImmutable::parse($first_slice['departure']);
                $second_departure_time = CarbonImmutable::parse($second_slice['departure']);

                if ($first_departure_time->equalTo($second_departure_time)) {
                    throw new Exception('Slices have equal departure time');
                }

                if ($first_departure_time->isBefore($second_departure_time)) {
                    return -1;
                }

                return 1;
            })
            ->first();

        $origin_airport = Airport::where('iata', '=', $earliest_slice['origin']['code'])->sole();
        $destination_airport = Airport::where('iata', '=', $earliest_slice['destination']['code'])->sole();

        return 'Origin: '.implode(
            ', ',
            array_filter([$origin_airport->city, $origin_airport->state, $origin_airport->country])
        ).'; Destination: '.implode(
            ', ',
            array_filter([$destination_airport->city, $destination_airport->state, $destination_airport->country])
        );
    }

    /**
     * Return whether this itinerary has any stops outside the United States.
     *
     * @phan-suppress PhanTypeMismatchReturn
     */
    public static function hasStopOutsideUnitedStates(array $decoded_itinerary): bool
    {
        return collect($decoded_itinerary['itinerary']['slices'])
            ->reduce(
                static fn (bool $carry, array $slice): bool => $carry || collect($slice['segments'])
                    ->reduce(
                        static fn (bool $carry, array $segment): bool => $carry || collect($segment['legs'])
                            ->reduce(
                                static fn (bool $carry, array $leg): bool => $carry ||
                                    Airport::where('iata', '=', $leg['origin']['code'])
                                        ->sole()
                                        ->country !== 'US' ||
                                    Airport::where('iata', '=', $leg['destination']['code'])
                                        ->sole()
                                        ->country !== 'US',
                                false
                            ),
                        false
                    ),
                false
            );
    }
}
