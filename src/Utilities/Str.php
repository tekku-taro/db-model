<?php
namespace Taro\DBModel\Utilities;

class Str
{
    public static function startWith(string $needle, string $haystack):bool
    {
        return strpos($haystack, $needle) === 0 ;
    }

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

    public static function pascalCase(string $name):string
    {
        $name = Inflect::singularize($name);
        $name = strtolower($name);
        $name = str_replace('_', ' ', $name);
        $name = ucwords($name);
        $name = str_replace(' ', '', $name);
        return $name;
    }


    public static function modifyOperatorIfNull($operator, $value)
    {
        $modified = $operator;
        if($value === 'NULL') {
            if($operator === '=') {
                $modified = 'IS';
            }else if($operator === '!=' || $operator === '<>') {
                $modified = 'IS NOT';
            }

            return $modified;
        }

        return $operator;
    }    
}