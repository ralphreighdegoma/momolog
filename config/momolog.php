<?php

return [
    /*
    |--------------------------------------------------------------------------
    | MomoLog Debug Server URL
    |--------------------------------------------------------------------------
    |
    | The URL of your Node.js debug server. This is where all debug data
    | will be sent for real-time monitoring and analysis.
    |
    */
    'server_url' => env('MOMOLOG_SERVER_URL', 'http://localhost:9090/debug'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | The timeout in seconds for HTTP requests to the debug server.
    | Keep this low to minimize impact on your application performance.
    |
    */
    'timeout' => env('MOMOLOG_TIMEOUT', 1),

    /*
    |--------------------------------------------------------------------------
    | Enable/Disable MomoLog
    |--------------------------------------------------------------------------
    |
    | Set to false to completely disable MomoLog. When null, MomoLog will
    | auto-detect if you're in a development environment.
    |
    | Supported: true, false, null
    |
    */
    'enabled' => env('MOMOLOG_ENABLED', null),

    /*
    |--------------------------------------------------------------------------
    | Async Mode
    |--------------------------------------------------------------------------
    |
    | When true, requests are sent in fire-and-forget mode without waiting
    | for server response. This minimizes performance impact on your app.
    |
    */
    'async' => env('MOMOLOG_ASYNC', true),
];
