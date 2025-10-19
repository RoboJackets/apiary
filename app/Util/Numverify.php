<?php

declare(strict_types=1);

namespace App\Util;

use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Cache;
use Numverify\Api;
use Numverify\PhoneNumber\PhoneNumberInterface;

class Numverify
{
    public static function verifyPhoneNumber(string $phoneNumber): ?PhoneNumberInterface
    {
        $result = Cache::get('numverify_result_'.$phoneNumber);

        if ($result !== null) {
            return $result;
        }

        $result = self::callNumverifyApi($phoneNumber);

        if ($result !== null) {
            Cache::forever('numverify_result_'.$phoneNumber, $result);
        }

        return $result;
    }

    private static function callNumverifyApi(string $phoneNumber): ?PhoneNumberInterface
    {
        $numverify = new Api(
            accessKey: config('services.numverify_api_key'),
            useHttps: true
        );

        try {
            return $numverify->validatePhoneNumber(phoneNumber: $phoneNumber, countryCode: 'US');
        } catch (ClientException $exception) {
            if ($exception->getStatusCode() === 429) {
                return null;
            } else {
                throw $exception;
            }
        }
    }
}
