<?php

declare(strict_types=1);

namespace App\Util;

class Matrix
{
    public static function getHighestDisplayPrice(string $encoded_itinerary): ?float
    {
        $decoded = json_decode($encoded_itinerary, true);

        if (! is_array($decoded) || ! array_key_exists('pricings', $decoded) || ! is_array($decoded['pricings'])) {
            return null;
        }

        $highestDisplayPrice = null;

        foreach ($decoded['pricings'] as $pricing) {
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
}
