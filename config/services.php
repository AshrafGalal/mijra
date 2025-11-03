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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
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

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('FACEBOOK_REDIRECT_URI'),
        'base_url' => env('FACEBOOK_BASE_URL'),
        'scopes' => [
            'instagram_business_manage_messages',
            'whatsapp_business_manage_events',
            'instagram_manage_messages',
            'user_messenger_contact',
            'whatsapp_business_messaging',
            'instagram_manage_events',
            'email',
        ],
        'webhook_verify_token' => env('FACEBOOK_WEBHOOK_VERIFY_TOKEN'),

    ],

    'shopify' => [
        'client_id' => env('SHOPIFY_CLIENT_ID'),
        'client_secret' => env('SHOPIFY_CLIENT_SECRET'),
        'redirect' => env('SHOPIFY_REDIRECT_URI'),
        'base_url' => env('SHOPIFY_BASE_URL'),
        'scopes' => [
            'read_customers',
            'write_customers',
            'read_payment_customizations',
            'write_payment_customizations',
            'read_products',
            'write_products',
            'customer_read_orders',
            'customer_write_orders',
        ],
    ],

    'stripe' => [
        'publish_key' => env('STRIP_PUBLISH_KEY'),
        'secret_key' => env('STRIP_SECRET_KEY'),
        'webhook_secret' => env('STRIP_WEBHOOK_SECRET'),
    ],
    'whatsapp' => [
        'node_service_url' => env('WHATSAPP_NODE_SERVICE_URL', 'http://localhost:3000'),
        'api_secret_token' => env('WHATSAPP_API_SECRET_TOKEN'),
    ],
];
