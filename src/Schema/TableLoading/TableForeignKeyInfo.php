<?php
namespace Taro\DBModel\Schema\TableLoading;


class TableForeignKeyInfo
{
    public $tableName;
    public $name;
    public $columnName;
    public $referencedTable;
    public $referencedColumnName;    
    public $onUpdate;    
    public $onDelete;    
}