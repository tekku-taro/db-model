<?php
namespace Taro\DBModel\Schema\TableLoading;


class TableColumnInfo
{
    public $tableName;
    public $name;
    public $dataType;
    public $unsigned;
    public $numericPrecision;
    public $maxLength;

    /** @var bool */
    public $isNullable;

    public $default;
    
    /** @var bool */
    public $autoIncrement;
}