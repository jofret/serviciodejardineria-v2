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

    'recaptcha' => [
        'site_key' => env('RECAPTCHA_SITE_KEY'),
        'secret_key' => env('RECAPTCHA_SECRET_KEY'),
    ],

    'deepseek' => [
        'key' => env('DEEPSEEK_API_KEY'),
        'model' => env('DEEPSEEK_MODEL', 'deepseek-v4-flash'),
        'base_url' => env('DEEPSEEK_BASE_URL', 'https://api.deepseek.com'),
    ],

    'whatsapp_cloud_api' => [
        'token' => env('WHATSAPP_CLOUD_API_TOKEN'),
        'phone_number_id' => env('WHATSAPP_CLOUD_API_PHONE_NUMBER_ID'),
        'api_version' => env('WHATSAPP_CLOUD_API_VERSION', 'v21.0'),
        'verify_token' => env('WHATSAPP_CLOUD_API_VERIFY_TOKEN'),
        'app_secret' => env('WHATSAPP_CLOUD_API_APP_SECRET'),
    ],

    // API central de Altoparque: Claudia crea/actualiza Customer,
    // WhatsappConversation y WhatsappMessage acá en vez de en la base local.
    'altoparque' => [
        'api_url' => env('ALTOPARQUE_API_URL', 'https://altoparque.com/api'),
        'api_token' => env('ALTOPARQUE_API_TOKEN'),
    ],

    // Protege /api/posts (ver EnsureCentralApiToken): dirección inversa a
    // 'altoparque' de arriba — acá altoparque.com es el cliente y este sitio
    // el servidor. Mismo valor configurado en altoparque.com como
    // SATELLITE_API_TOKEN.
    'central_api' => [
        'token' => env('CENTRAL_API_TOKEN'),
    ],

];
