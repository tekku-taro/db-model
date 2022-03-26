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

/**
 * @param array<mixed> $a
 * @param array<mixed> $b
 * @return bool
 */
function twoArraysHaveSameElements(array $a, array $b)
{
    sort($a);
    sort($b);
    if($a == $b) {
        return true;
    }
    return false;
}