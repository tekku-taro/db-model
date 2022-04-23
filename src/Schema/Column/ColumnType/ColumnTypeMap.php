<?php
namespace Taro\DBModel\Schema\Column\ColumnType;

use Taro\DBModel\Exceptions\NotFoundException;

abstract class ColumnTypeMap
{
    /** @var array<string,string> */
    protected const TYPE_MAP = [];

    public const DEFAULT_CHAR_LENGTH = 255;

    public static function getDBType(string $typeName): string
    {
        if(static::includes($typeName)) {
            return static::TYPE_MAP[$typeName]['type'];
        }
    }

    public static function getTypeName(string $dbType): string
    {
        $dbType = strtoupper($dbType);
        $typeName = array_search(
            $dbType,
            array_filter(
                array_combine(
                    array_keys(static::TYPE_MAP),
                    array_column(
                        static::TYPE_MAP, 'type'
                    )
                )
            )
        );

        if($typeName === false) {
            throw new NotFoundException($dbType . ' データタイプは登録されていません');
        }
        return $typeName;
    }

    public static function checkHasLength(string $typeName): string
    {
        if(static::includes($typeName)) {
            return static::TYPE_MAP[$typeName]['length'];
        }

        return false;
    }

    public static function includes(string $typeName):bool
    {
        return in_array($typeName, array_keys(static::TYPE_MAP));
    }

    public static function isType(string $type):bool
    {
        return in_array($type, array_column(static::TYPE_MAP, 'type'));
    }

}