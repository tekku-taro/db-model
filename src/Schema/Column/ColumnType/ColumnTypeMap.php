<?php
namespace Taro\DBModel\Schema\Column\ColumnType;


abstract class ColumnTypeMap
{
    /** @var array<string,string> */
    private const TYPE_MAP = [];

    public static function getDBType(string $typeName): string
    {
        if(static::includes($typeName)) {
            return self::TYPE_MAP[$typeName]['type'];
        }
    }

    public static function checkHasLength(string $typeName): string
    {
        if(static::includes($typeName)) {
            return self::TYPE_MAP[$typeName]['length'];
        }

        return false;
    }

    public static function includes(string $typeName):bool
    {
        return in_array($typeName, array_keys(static::TYPE_MAP));
    }

}