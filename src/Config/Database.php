<?php
namespace Taro\DBModel\Config;

return [
    'default'=>'mysql',
    'connections' => [
        'mysql'=>[
            'driver'=>env('DB_DRIVER', 'mysql'),
            'host'=>env('DB_HOST', 'localhost'),
            'user'=>env('DB_USER', 'root'),
            'password'=>env('DB_PASSWORD', ''),
            'dbname'=>env('DB_NAME', 'tasksdb'),
        ],
    ]
];
