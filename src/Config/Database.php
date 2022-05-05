<?php
namespace Taro\DBModel\Config;

use Taro\DBModel\Utilities\FileHandler;

return [
    'default'=>'mysql',
    'connections' => [
        'mysql'=>[
            'driver'=>'mysql',
            'host'=>'localhost',
            'user'=>'root',
            'password'=>'',
            'dbname'=>'tasksdb',
        ],
        'sqlite'=>[
            'driver'=>'sqlite',
            'dsn'=>'sqlite:' . FileHandler::SQLITE_PATH,
        ],
    ]
];
