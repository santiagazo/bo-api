<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | AWS Pinpoint SMS (10DLC Compliant)
    |--------------------------------------------------------------------------
    |
    | Uses AWS End User Messaging SMS (formerly Pinpoint SMS) for A2P messaging.
    | Much cheaper than Twilio: $0.50/campaign/month vs $15/campaign/month.
    |
    */
    'aws_sms' => [
        'key' => env('AWS_SMS_ACCESS_KEY_ID', env('AWS_ACCESS_KEY_ID')),
        'secret' => env('AWS_SMS_SECRET_ACCESS_KEY', env('AWS_SECRET_ACCESS_KEY')),
        'region' => env('AWS_SMS_REGION', 'us-east-1'),
        'origination_number' => env('AWS_SMS_ORIGINATION_NUMBER'), // Your 10DLC number
        'force_send' => env('AWS_SMS_FORCE_SEND', false),
    ],

];
