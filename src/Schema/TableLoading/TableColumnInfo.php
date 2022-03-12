<?php
namespace Taro\DBModel\Schema\TableLoading;

use Taro\DBModel\DB\DbManipulator;

class TableColumnInfo
{
    private $tableName;
    private $name;
    private $pk;
    private $dataType;
    private $maxLength;
    private $isNullable;
    private $default;
    private $autoIncrement;
    private $fk;
    private $uk;
    private $index;
}