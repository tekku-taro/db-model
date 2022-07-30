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
        'pgsql'=>[
            'driver'=>'pgsql',
            'host'=>'localhost',
            'user'=>'postgres',
            'password'=>'password',
            'dbname'=>'tasksdb',
            'port'=>5433,
        ],
        'sqlite'=>[
            'driver'=>'sqlite',
            'dsn'=>'sqlite:' . FileHandler::SQLITE_PATH,
        ],
    ]
];
