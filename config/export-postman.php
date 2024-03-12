<?php

return [
    'base_url' => env('APP_URL', 'http://localhost'),
    'base_url_key' => 'APP_URL',
    'disk' => 'local',
    'path' => 'postman/{timestamp}_{app}_collection.json',
    'collection_name' => '{app}',
    'headers' => [
        [
            'key' => 'Accept',
            'value' => 'application/json',
        ],
        [
            'key' => 'Content-Type',
            'value' => 'application/json',
        ],
    ],
    'formdata' => [
//         'email' => 'test@example.com',
//         'password' => 'password',
    ],
    'include_middleware' => ['api'],
    'folders' => [

    ]
];
