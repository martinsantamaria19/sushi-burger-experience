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

    'n8n' => [
        'password_reset_webhook' => env('N8N_PASSWORD_RESET_WEBHOOK'),
        'subscription_purchased_webhook' => env('N8N_SUBSCRIPTION_PURCHASED_WEBHOOK', 'https://n8n.example.com/webhook/subscription-purchased'),
        'subscription_payment_failed_webhook' => env('N8N_SUBSCRIPTION_PAYMENT_FAILED_WEBHOOK', 'https://n8n.example.com/webhook/subscription-payment-failed'),
        'subscription_cancelled_webhook' => env('N8N_SUBSCRIPTION_CANCELLED_WEBHOOK', 'https://n8n.example.com/webhook/subscription-cancelled'),
    ],

    'mercadopago' => [
        'access_token' => env('MP_ACCESS_TOKEN'),
        'public_key' => env('MP_PUBLIC_KEY'),
        'app_id' => env('MP_APP_ID', '1029790359644016'), // Sushi Burger Experience App ID
        'webhook_secret' => env('MP_WEBHOOK_SECRET'),
        'environment' => env('MP_ENVIRONMENT', 'sandbox'), // sandbox or production
    ],

    'google_maps' => [
        'api_key' => env('GOOGLE_MAPS_API_KEY'),
    ],

];
