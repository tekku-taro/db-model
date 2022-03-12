<?php
namespace Taro\DBModel\Schema\MySql\Column\ColumnType;

use Taro\DBModel\Schema\Column\ColumnType\ColumnTypeMap;

class MySqlColumnTypeMap extends ColumnTypeMap
{
    /** @var array<string,string> */
    private const TYPE_MAP = [
        'int' => ['type'=>'INT','length'=> false],
        'integer' => ['type'=>'INT','length'=> false],
        'float' => ['type'=>'FLOAT','length'=> false],
        'decimal' => ['type'=>'DECIMAL','length'=> false],
        'double' => ['type'=>'DOUBLE','length'=> false],
        'smallInt' => ['type'=>'SMALLINT','length'=> false],
        'mediumInt' => ['type'=>'MEDIUMINT','length'=> false],
        'bigInt' => ['type'=>'BIGINT','length'=> false],
        'bool' => ['type'=>'TINYINT','length'=> false],
        'char' => ['type'=>'CHAR','length'=> true],
        'varchar' => ['type'=>'VARCHAR','length'=> true],
        'text' => ['type'=>'TEXT','length'=> false],
        'blob' => ['type'=>'BLOB','length'=> false],
        'date' => ['type'=>'ENUM','length'=> false],
        'datetime' => ['type'=>'DATETIME','length'=> false],
        'time' => ['type'=>'TIME','length'=> false],
        'timestamp' => ['type'=>'TIMESTAMP','length'=> false],
    ];
}