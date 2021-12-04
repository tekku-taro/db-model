<?php

use Taro\DBModel\Http\Request;

function env($name, $default = null)
{
    if(isset($_ENV[$name])) {
        return $_ENV[$name];
    }

    return $default;
}

function request()
{
    return Request::getInstance();
}