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
use Taro\DBModel\Schema\PostgreSql\Column\ColumnType\PostgreSqlColumnTypeMap;
use Taro\DBModel\Schema\PostgreSql\Column\PostgreSqlColumn;
use Taro\DBModel\Schema\PostgreSql\Column\PostgreSqlForeignKey;
use Taro\DBModel\Schema\PostgreSql\Column\PostgreSqlIndex;
use Taro\DBModel\Schema\PostgreSql\Column\PostgreSqlPrimaryKey;
use Taro\DBModel\Schema\PostgreSql\PostgreSqlTable;
use Taro\DBModel\Schema\Sqlite\Column\ColumnType\SqliteColumnTypeMap;
use Taro\DBModel\Schema\Sqlite\Column\SqliteColumn;
use Taro\DBModel\Schema\Sqlite\Column\SqliteForeignKey;
use Taro\DBModel\Schema\Sqlite\Column\SqliteIndex;
use Taro\DBModel\Schema\Sqlite\Column\SqlitePrimaryKey;
use Taro\DBModel\Schema\Sqlite\SqliteTable;

class SchemaFactory
{
    public static function newTable(string $name, DbDriver $driver)
    {        
        switch ($driver->type) {
            case DbDriver::MY_SQL:
                return new MySqlTable($name);
            case DbDriver::POSTGRE_SQL:
                return new PostgreSqlTable($name);
            case DbDriver::SQLITE:
                return new SqliteTable($name);
        }  
    }

    public static function newColumn(DbDriver $driver, string $name, string $dbType, string $tableName)
    {   
        switch ($driver->type) {
            case DbDriver::MY_SQL:
                $type = MySqlColumnTypeMap::getTypeName($dbType);
                return new MySqlColumn(Column::ADD_ACTION, $name, $type, $tableName);
            case DbDriver::POSTGRE_SQL:
                $type = PostgreSqlColumnTypeMap::getTypeName($dbType);
                return new PostgreSqlColumn(Column::ADD_ACTION, $name, $type, $tableName);
            case DbDriver::SQLITE:
                $type = SqliteColumnTypeMap::getTypeName($dbType);
                return new SqliteColumn(Column::ADD_ACTION, $name, $type, $tableName);
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
            case DbDriver::POSTGRE_SQL:
                return new PostgreSqlForeignKey(ForeignKey::ADD_ACTION, $columnName, $tableName);                
            case DbDriver::SQLITE:
                return new SqliteForeignKey(ForeignKey::ADD_ACTION, $columnName, $tableName);

        }  
    }

    /**
     * @param DbDriver $driver
     * @param array<string> $columnNames
     * @param string $tableName
     * @return PrimaryKey
     */
    public static function newPrimaryKey(DbDriver $driver, $columnNames = [], string $tableName)
    {        
        switch ($driver->type) {
            case DbDriver::MY_SQL:
                return new MySqlPrimaryKey(PrimaryKey::ADD_ACTION, $columnNames, $tableName);
            case DbDriver::SQLITE:
                return new SqlitePrimaryKey(PrimaryKey::ADD_ACTION, $columnNames, $tableName);
            case DbDriver::POSTGRE_SQL:
                return new PostgreSqlPrimaryKey(PrimaryKey::ADD_ACTION, $columnNames, $tableName);
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
            case DbDriver::POSTGRE_SQL:
                return new PostgreSqlIndex(Index::ADD_ACTION, $columnNames, $tableName);
            case DbDriver::SQLITE:
                return new SqliteIndex(Index::ADD_ACTION, $columnNames, $tableName);
        }  
    }


}