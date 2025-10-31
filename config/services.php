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
        'base_url' => env('FACEBOOK_BASE_URL', 'https://graph.facebook.com'),
        'page_access_token' => env('FACEBOOK_PAGE_ACCESS_TOKEN'),
        'verify_token' => env('FACEBOOK_VERIFY_TOKEN'),
        'scopes' => [
            'pages_messaging',
            'pages_manage_metadata',
            'instagram_business_manage_messages',
            'whatsapp_business_manage_events',
            'instagram_manage_messages',
            'user_messenger_contact',
            'whatsapp_business_messaging',
            'instagram_manage_events',
            'email',
        ],
    ],

    'shopify' => [
        'client_id' => env('SHOPIFY_CLIENT_ID'),
        'client_secret' => env('SHOPIFY_CLIENT_SECRET'),
        'redirect' => env('SHOPIFY_REDIRECT_URI'),
        'base_url' => env('SHOPIFY_BASE_URL'),
        'webhook_secret' => env('SHOPIFY_WEBHOOK_SECRET'),
        'scopes' => [
            'read_customers',
            'write_customers',
            'read_payment_customizations',
            'write_payment_customizations',
            'read_products',
            'write_products',
            'read_orders',
            'write_orders',
        ],
    ],

    'stripe' => [
        'publish_key' => env('STRIP_PUBLISH_KEY'),
        'secret_key' => env('STRIP_SECRET_KEY'),
        'webhook_secret' => env('STRIP_WEBHOOK_SECRET'),
    ],

    'whatsapp' => [
        'api_version' => env('WHATSAPP_API_VERSION', 'v21.0'),
        'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
        'business_account_id' => env('WHATSAPP_BUSINESS_ACCOUNT_ID'),
        'access_token' => env('WHATSAPP_ACCESS_TOKEN'),
        'app_secret' => env('WHATSAPP_APP_SECRET'),
        'verify_token' => env('WHATSAPP_VERIFY_TOKEN'),
        'base_url' => env('WHATSAPP_BASE_URL', 'https://graph.facebook.com'),
    ],

    'twilio' => [
        'sid' => env('TWILIO_SID'),
        'token' => env('TWILIO_TOKEN'),
        'from' => env('TWILIO_FROM'), // Your Twilio phone number
    ],

    'tiktok' => [
        'app_id' => env('TIKTOK_APP_ID'),
        'app_secret' => env('TIKTOK_APP_SECRET'),
        'access_token' => env('TIKTOK_ACCESS_TOKEN'),
        'verify_token' => env('TIKTOK_VERIFY_TOKEN'),
        'base_url' => env('TIKTOK_BASE_URL', 'https://business-api.tiktok.com'),
    ],

    'salla' => [
        'client_id' => env('SALLA_CLIENT_ID'),
        'client_secret' => env('SALLA_CLIENT_SECRET'),
        'redirect' => env('SALLA_REDIRECT_URI'),
        'webhook_secret' => env('SALLA_WEBHOOK_SECRET'),
        'base_url' => env('SALLA_BASE_URL', 'https://api.salla.sa'),
    ],

    'woocommerce' => [
        'webhook_secret' => env('WOOCOMMERCE_WEBHOOK_SECRET'),
        'consumer_key' => env('WOOCOMMERCE_CONSUMER_KEY'),
        'consumer_secret' => env('WOOCOMMERCE_CONSUMER_SECRET'),
        'store_url' => env('WOOCOMMERCE_STORE_URL'),
    ],

    'gmb' => [
        'service_account_key' => env('GMB_SERVICE_ACCOUNT_KEY'),
        'access_token' => env('GMB_ACCESS_TOKEN'),
        'base_url' => env('GMB_BASE_URL', 'https://businessmessages.googleapis.com/v1'),
    ],

    'pymob' => [
        'api_key' => env('PYMOB_API_KEY'),
        'secret_key' => env('PYMOB_SECRET_KEY'),
        'base_url' => env('PYMOB_BASE_URL', 'https://api.pymob.com'),
    ],

    'moyasar' => [
        'api_key' => env('MOYASAR_API_KEY'),
        'secret_key' => env('MOYASAR_SECRET_KEY'),
        'base_url' => env('MOYASAR_BASE_URL', 'https://api.moyasar.com'),
    ],
];
