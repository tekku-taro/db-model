<?php
namespace Taro\DBModel\Schema;

use Taro\DBModel\Schema\Column\Column;
use Taro\DBModel\Schema\Column\ForeignKey;
use Taro\DBModel\Schema\Column\Index;
use Taro\DBModel\Schema\Column\PrimaryKey;
use Taro\DBModel\Schema\MySql\Column\ColumnType\MySqlColumnTypeMap;
use Taro\DBModel\Schema\MySql\Column\MySqlColumn;
use Taro\DBModel\Schema\MySql\Column\MySqlForeignKey;
use Taro\DBModel\Schema\MySql\Column\MySqlIndex;
use Taro\DBModel\Schema\MySql\Column\MySqlPrimaryKey;
use Taro\DBModel\Schema\MySql\MySqlTable;

class SchemaFactory
{
    public static function newTable(string $name, DbDriver $driver)
    {        
        switch ($driver->type) {
            case DbDriver::MY_SQL:
                return new MySqlTable($name);
        }  
    }

    public static function newColumn(DbDriver $driver, string $name, string $dbType, string $tableName)
    {   
        switch ($driver->type) {
            case DbDriver::MY_SQL:
                $type = MySqlColumnTypeMap::getTypeName($dbType);
                return new MySqlColumn(Column::ADD_ACTION, $name, $type, $tableName);
        }  
    }

    /**
     * @param DbDriver $driver
     * @param string $columnName
     * @param string $tableName
     * @return ForeignKey
     */
    public static function newForeignKey(DbDriver $driver,string $columnName, string $tableName)
    {        
        switch ($driver->type) {
            case DbDriver::MY_SQL:
                return new MySqlForeignKey(ForeignKey::ADD_ACTION, $columnName, $tableName);
        }  
    }

    /**
     * @param DbDriver $driver
     * @param array<string> $columnNames
     * @return PrimaryKey
     */
    public static function newPrimaryKey(DbDriver $driver, $columnNames = [])
    {        
        switch ($driver->type) {
            case DbDriver::MY_SQL:
                return new MySqlPrimaryKey(PrimaryKey::ADD_ACTION, $columnNames);
        }  
    }

    /**
     * @param DbDriver $driver
     * @param array<string> $columnNames
     * @param string $tableName
     * @return Index
     */
    public static function newIndex(DbDriver $driver, $columnNames = [], string $tableName)
    {        
        switch ($driver->type) {
            case DbDriver::MY_SQL:
                return new MySqlIndex(Index::ADD_ACTION, $columnNames, $tableName);
        }  
    }


}