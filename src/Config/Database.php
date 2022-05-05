<?php
namespace Taro\DBModel\Config;

use Taro\DBModel\Utilities\FileHandler;

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
        'sqlite'=>[
            'driver'=>env('DB_DRIVER', 'sqlite'),
            'dsn'=>env('DB_DSN', 'sqlite:' . FileHandler::SQLITE_PATH),
        ],
    ]
];
