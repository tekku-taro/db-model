<?php
namespace Taro\DBModel\Schema;

use Taro\DBModel\DB\DB;
use Taro\DBModel\DB\DbManipulator;
use Taro\DBModel\Schema\MySql\MySqlTable;
use Taro\DBModel\Schema\MySql\MySqlTableFetcher;
use Taro\DBModel\Schema\TableLoading\TableLoader;

class Schema
{
    private static $connName;

    private static function useTable(DbManipulator $dbManipulator):void
    {
        $config = DB::getConfig(self::$connName);      
        $dbManipulator->exec('USE ' . $config['dbname'] . ';');       
    }

    public static function createTable(string $name, Callable $callback)
    {        
        $dbManipulator = self::getDbManipulator();
        self::useTable($dbManipulator);
        $config = DB::getConfig(self::$connName);
        $driver = new DbDriver($config['driver'],$config['dbname']);
        switch ($driver->type) {
            case DbDriver::MY_SQL:
                $table = new MySqlTable($name);
                break;
        }
        $callback($table);
        $sql = $table->generateSql(Table::CREATE_MODE);
        
        return $dbManipulator->exec($sql);        
    }

    public static function getTable(string $name)    
    {
        $config = DB::getConfig(self::$connName);
        $driver = new DbDriver($config['driver'],$config['dbname']);
        return self::loadTableInfo($name, $driver);
    }

    public static function alterTable(Table $table)    
    {
        $dbManipulator = self::getDbManipulator();
        self::useTable($dbManipulator);
        $sql = $table->generateSql(Table::ALTER_MODE);
        return $dbManipulator->exec($sql); 
    }

    public static function dropTableIfExists(string $name)    
    {
        $dbManipulator = self::getDbManipulator();
        self::useTable($dbManipulator);
        $sql = 'DROP TABLE IF EXISTS ' . $name;
        return $dbManipulator->exec($sql); 
    }

    private static function loadTableInfo(string $name, DbDriver $driver)    
    {
        switch ($driver->type) {
            case DbDriver::MY_SQL:
                /** @var array<TableColumnInfo> $tableData */
                $tableData = MySqlTableFetcher::fetchInfo($name, $driver, self::getDbManipulator());

                $table = new MySqlTable($name);
                $table = TableLoader::load($table, $tableData);
                return self::setOriginal($table);
                break;
        }
    }

    private function setOriginal(Table $originalTable)
    {
        /** @var Table $table */
        $table = new ${get_class($originalTable)};
        $table->original = $originalTable;
        return $table;
    }

    protected static function getDbManipulator(): DbManipulator
    {
        if(self::$connName === null) {
            return DB::getGlobal()->getManipulator();

        }
        return DB::database(self::$connName)->getManipulator();      
    }  

    public static function setConnection(string $connectionName)
    {
        self::$connName = $connectionName;
    }

    public static function defaultConnection()
    {
        self::$connName = null;
    }

}