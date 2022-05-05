<?php
namespace Taro\DBModel\Schema\Sqlite\Column\ColumnType;

use Taro\DBModel\Schema\Column\ColumnType\ColumnTypeMap;

class SqliteColumnTypeMap extends ColumnTypeMap
{
    /** @var array<string,array<string,mixed>> */
    protected const TYPE_MAP = [
        'int' => ['type'=>'INTEGER','length'=> false],
        'integer' => ['type'=>'INTEGER','length'=> false],
        'float' => ['type'=>'REAL','length'=> false],
        'decimal' => ['type'=>'NUMERIC','length'=> false],
        'double' => ['type'=>'REAL','length'=> false],
        'smallInt' => ['type'=>'INTEGER','length'=> false],
        'mediumInt' => ['type'=>'INTEGER','length'=> false],
        'bigInt' => ['type'=>'INTEGER','length'=> false],
        'bool' => ['type'=>'INTEGER','length'=> false],
        'char' => ['type'=>'TEXT','length'=> false],
        'varchar' => ['type'=>'TEXT','length'=> false],
        'string' => ['type'=>'TEXT','length'=> false],
        'text' => ['type'=>'TEXT','length'=> false],
        'blob' => ['type'=>'BLOB','length'=> false],
        'date' => ['type'=>'TEXT','length'=> false],
        'datetime' => ['type'=>'TEXT','length'=> false],
        'time' => ['type'=>'TEXT','length'=> false],
        'timestamp' => ['type'=>'TEXT','length'=> false],
    ];
}