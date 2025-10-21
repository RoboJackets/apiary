<?php

declare(strict_types=1);

return [

    /*
     * The notification that will be sent when a job fails.
     */
    'notification' => \App\Util\JobFailureNotification::class,

    /*
     * The notifiable to which the notification will be sent. The default
     * notifiable will use the mail and slack configuration specified
     * in this config file.
     */
    'notifiable' => \Spatie\FailedJobMonitor\Notifiable::class,

    /*
     * By default notifications are sent for all failures. You can pass a callable to filter
     * out certain notifications. The given callable will receive the notification. If the callable
     * return false, the notification will not be sent.
     */
    'notificationFilter' => null,

    /*
     * The channels to which the notification will be sent.
     */
    'channels' => env('FAILED_JOB_SLACK_WEBHOOK_URL') === null ? [] : ['slack'],

    'mail' => [
        'to' => env('FAILED_JOB_EMAIL_ADDRESS'),
    ],

    'slack' => [
        'webhook_url' => env('FAILED_JOB_SLACK_WEBHOOK_URL'),
    ],
];
