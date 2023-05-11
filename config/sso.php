<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Laravel SSO Settings
    |--------------------------------------------------------------------------
    |
    | Set type of this web page. Possible options are: 'server' and 'broker'.
    | You must specify either 'server' or 'broker'.
    |
    */

    'type' => 'server',

    /*
    |--------------------------------------------------------------------------
    | Settings necessary for the SSO server.
    |--------------------------------------------------------------------------
    |
    | These settings should be changed if this page is working as SSO server.
    |
    */

    'users_model' => \App\Models\User::class,
    'brokers_model' => Esyede\SSO\Models\Broker::class,

    // Table used in Esyede\SSO\Models\Broker model
    'brokers_table' => 'brokers',

    // Whether multi fields used for authentication
    'multi_enabled' => env('SSO_MULTI_ENABLED', false),

    // User model relationship
    'use_relationship' => env('SSO_WITH_RELATIONSHIP', false),
    'relation_name' => '',

    /*
    |--------------------------------------------------------------------------
    | Settings necessary for the SSO broker.
    |--------------------------------------------------------------------------
    |
    | These settings should be changed if this page is working as SSO broker.
    |
    */

    'serverUrl' => env('SSO_SERVER_URL', null),
    'broker_name' => env('SSO_BROKER_NAME', null),
    'broker_secret' => env('SSO_BROKER_SECRET', null),
    'api' => [
        'enabled' => env('SSO_API_ENABLED', true),
    ],

];
