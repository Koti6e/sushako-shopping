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

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    'whatsapp' => [
        'support_number' => env('SUSHAKO_WHATSAPP_NUMBER', '919876543210'),
        'support_message' => env('SUSHAKO_WHATSAPP_MESSAGE', "Hello Sushako Shopping,\nI need assistance with my order."),
    ],

    'razorpay' => [
        'key' => env('RAZORPAY_KEY_ID', 'YOUR_KEY_ID'),
        'secret' => env('RAZORPAY_KEY_SECRET'),
        'currency' => env('RAZORPAY_CURRENCY', 'INR'),
        'callback_url' => env('RAZORPAY_CALLBACK_URL'),
        'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET'),
        'test_order_id' => env('RAZORPAY_TEST_ORDER_ID'),
        'test_mode' => env('RAZORPAY_TEST_MODE', true),
    ],

];
