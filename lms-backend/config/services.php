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
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'app_central' => env('APP_CENTRAL'),
    
    // External payment gateway (checkout forwarder, status check, official receipt)
    'payment_gateway' => [
        'url' => rtrim(env('PAYMENT_GATEWAY_URL', 'https://payments.example.com'), '/'),
        'secret' => env('PAYMENT_GATEWAY_SECRET', ''),
    ],

    // Origin of the frontend, allowed to embed API responses in a frame
    'frontend_url' => env('FRONTEND_URL', ''),

    'super_admin' => [
        'email' => env('SUPER_ADMIN_EMAIL', 'admin@example.com'),
        'password' => env('SUPER_ADMIN_PASSWORD'),
    ],

    "sender" => [
      "key" => env("RESEND_API_KEY")
    ]

];
