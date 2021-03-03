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

    'chat' => [
        'domain'=> env('CHAT_SERVICE_DOMAIN'),
        'apiKey' => 'AIzaSyBoyanen_bS8Y2izhDq8R2KrpBJ9enfMQo',
        'authDomain' => 'neucrad-b797d.firebaseapp.com',
        'databaseURL' => 'https://neucrad-b797d.firebaseio.com',
        'projectId' => 'neucrad-b797d',
        'storageBucket' => 'neucrad-b797d.appspot.com',
        'messagingSenderId' => '734640782362',
        'appId' => '1:734640782362:web:5d2919da1fb402cfc425a3',
        'measurementId' => 'G-C33943CNQ9',
    ]

];
