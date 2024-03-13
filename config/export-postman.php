<?php

return [
    // base url
    'base_url' => env('APP_URL', 'http://localhost'),
    // postman variable (example: {{APP_URL}}/...)
    'base_url_key' => 'APP_URL',
    // Storage disk
    'disk' => 'local',
    // path for create
    'path' => 'postman/{timestamp}_{app}_collection.json',
    // collection name for postman
    'collection_name' => '{app}',
    // headers for every request
    'headers' => [
        // 'key' => 'value',
    ],
    // or params type form "form-data", "x-www-form-urlencoded", "json"
    'content_type' => 'form-data',
    // value for this parameter name
    'params_value' => [
        'email' => 'test@example.com',
        'password' => 'password',
    ],
    // requirement middlewares for requests
    'include_middleware' => ['api'],
    // setting paths for postman
    'folders' => [
        'api' => [
            'isGlobal' => true, // the global path
            'level' => 2, // maximum folder level
            'prerequest' => '', // prerequest scripts in javascript
            'test' => '', // test scripts in javascript
            'auth' => [
                'type' => 'bearer', // auth type
                'bearer' => [ ['key'=>'token', 'value'=>'{{TOKEN}}', 'type' => 'string'] ] // auth key
            ],
        ]
    ]
];
