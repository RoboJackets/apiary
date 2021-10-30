<?php

declare(strict_types=1);

namespace App\Sentry;

use Sentry\Event;

class Helpers
{
    public static function beforeSend(Event $event): Event
    {
        $request = $event->getRequest();

        if (array_key_exists('data', $request) && array_key_exists('refresh_token', $request['data'])) {
            $request['data']['refresh_token'] = '[redacted]';
        }

        $event->setRequest($request);

        return $event;
    }
}
