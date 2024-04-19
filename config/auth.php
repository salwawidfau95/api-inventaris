<?php

return [
    'defaults' => [
        'guard' => 'api', //nama middleware
        'passwords' => 'users',
    ],

    //nama middleware
    'guards' => [
        'api' => [
            'driver' => 'jwt',
            'provider' => 'users',
        ],
    ],

    //posisi letak model 
    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => \App\Models\User::class
        ]
    ]
];