<?php
namespace Taro\DBModel\Schema\PostgreSql\Column\ColumnType;

use Taro\DBModel\Schema\Column\ColumnType\ColumnTypeMap;

class PostgreSqlColumnTypeMap extends ColumnTypeMap
{
    /** @var array<string,array<string,mixed>> */
    protected const TYPE_MAP = [
        'int' => ['type'=>'INTEGER','length'=> false],
        'integer' => ['type'=>'INTEGER','length'=> false],
        'float' => ['type'=>'REAL','length'=> false],
        'decimal' => ['type'=>'DECIMAL','length'=> false],
        'double' => ['type'=>'DOUBLE PRECISION','length'=> false],
        'smallInt' => ['type'=>'SMALLINT','length'=> false],
        'mediumInt' => ['type'=>'INTEGER','length'=> false],
        'bigInt' => ['type'=>'BIGINT','length'=> false],
        'bool' => ['type'=>'BOOLEAN','length'=> false],
        'char' => ['type'=>'CHAR','length'=> true],
        'varchar' => ['type'=>'VARCHAR','length'=> true],
        'string' => ['type'=>'VARCHAR','length'=> true],
        'text' => ['type'=>'TEXT','length'=> false],
        'blob' => ['type'=>'bytea','length'=> false],
        'date' => ['type'=>'DATE','length'=> false],
        'datetime' => ['type'=>'TIMESTAMP','length'=> false],
        'time' => ['type'=>'TIME','length'=> false],
        'timestamp' => ['type'=>'TIMESTAMP','length'=> false],
    ];
}