<?php
namespace Taro\DBModel\Utilities;

class Str
{
    public static function getShortClassName(string $classWithNamespace):string
    {
        return substr($classWithNamespace, strrpos($classWithNamespace, '\\') + 1);
    }

    public static function snakeCase(string $name):string
    {
        $result = preg_replace('/([A-Z])/', '_$1', $name);
        $result = strtolower($result);
        return ltrim($result, '_');
    }

}