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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'livekit' => [
        'host' => env('LIVEKIT_HOST'),
        'api_key' => env('LIVEKIT_API_KEY'),
        'api_secret' => env('LIVEKIT_API_SECRET'),
    ],

    'sslcommerz' => [
        'store_id' => env('SSLCZ_STORE_ID'),
        'store_password' => env('SSLCZ_STORE_PASSWORD'),
        'base_url' => env('SSLCZ_TESTMODE') ? 'https://sandbox.sslcommerz.com' : 'https://securepay.sslcommerz.com',
        'success_url' => env('SSLCZ_SUCCESS_URL'),
        'fail_url' => env('SSLCZ_FAIL_URL'),
        'cancel_url' => env('SSLCZ_CANCEL_URL'),
        'ipn_url' => env('SSLCZ_IPN_URL'),
    ],

    'imgproxy' => [
        'signature_size' => env('IMGPROXY_SIGNATURE_SIZE', 32),
        'key' => env('IMGPROXY_KEY'),
        'salt' => env('IMGPROXY_SALT'),
        'secret' => env('IMGPROXY_SECRET'),
        'base_url' => env('IMGPROXY_URL'),
    ],

    'pathao' => [
        'base_url' => env('PATHAO_API_BASE'),
        'client_id' => env('PATHAO_CLIENT_ID'),
        'client_secret' => env('PATHAO_CLIENT_SECRET'),
        'client_email' => env('PATHAO_CLIENT_EMAIL'),
        'client_password' => env('PATHAO_CLIENT_PASSWORD'),
        'grant_type' => env('PATHAO_GRANT_TYPE', 'password'),
        'webhook_secret' => env('PATHAO_WEBHOOK_SECRET'),
    ],
];
