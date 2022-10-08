<?php
namespace Taro\DBModel\Schema;

use Taro\DBModel\DB\DB;
use Taro\DBModel\DB\DbManipulator;
use Taro\DBModel\Schema\TableLoading\TableFetcher;
use Taro\DBModel\Schema\TableLoading\TableLoader;

class Schema
{
    private static $connName;

    private static function useDB(DbManipulator $dbManipulator, DbDriver $driver):void
    {
        $config = DB::getConfig(self::$connName);      
        if(isset($config['dbname'])) {
            $dbManipulator->exec(DBCommandFactory::useDB($config, $driver));
        }
    }

    public static function createTable(string $name, Callable $callback):Table
    {        
        $dbManipulator = self::getDbManipulator();
        $driver = self::getDriver();
        self::useDB($dbManipulator, $driver);
        $table = SchemaFactory::newTable($name, $driver);
        $callback($table);
        $sql = $table->generateSql(Table::CREATE_MODE);
    
        $dbManipulator->exec($sql);        
        return self::getTable($table->name);
    }

    public static function getTable(string $name):Table
    {        
        $driver = self::getDriver();
        return self::loadTableInfo($name, $driver);
    }

    public static function alterTable(Table $table):Table   
    {
        $dbManipulator = self::getDbManipulator();
        $driver = self::getDriver();
        self::useDB($dbManipulator, $driver);
        $sql = $table->generateSql(Table::ALTER_MODE);
        $dbManipulator->exec($sql); 
        return self::getTable($table->name);
    }

    public static function saveTable(string $name, Callable $callback):Table   
    {
        $dbManipulator = self::getDbManipulator();
        $driver = self::getDriver();
        self::useDB($dbManipulator, $driver);

        $fetcher = TableFetcher::getTableFetcher($name, $driver, $dbManipulator);
        
        if(!$fetcher) {
            $table = SchemaFactory::newTable($name, $driver);
            $callback($table);            
            $sql = $table->generateSql(Table::CREATE_MODE);
        } else {
            $table = self::getTable($name);
            $callback($table); 
            $table->diffNewOriginalComponentsForSave();
            $sql = $table->generateSql(Table::ALTER_MODE);
        }    
        $dbManipulator->exec($sql);        

        return self::getTable($table->name);
    }

    public static function dropTable(Table $table)    
    {
        $dbManipulator = self::getDbManipulator();
        $driver = self::getDriver();
        self::useDB($dbManipulator, $driver);
        $sql = $table->generateSql(Table::DROP_MODE);
        return $dbManipulator->exec($sql); 
    }

    public static function dropTableIfExists(string $name)    
    {
        $dbManipulator = self::getDbManipulator();
        $driver = self::getDriver();
        self::useDB($dbManipulator, $driver);
        $sql = 'DROP TABLE IF EXISTS ' . $name;
        return $dbManipulator->exec($sql); 
    }

    private static function loadTableInfo(string $name, DbDriver $driver)    
    {
        /** @var TableFetcher $fetcher */
        $fetcher = TableFetcher::fetchInfo($name, $driver, self::getDbManipulator());

        $loader = new TableLoader($name, $driver, $fetcher);
        $table = $loader->load();
        return self::setOriginal($table);

    }


    private function setOriginal(Table $originalTable)
    {
        /** @var Table $table */
        $class = get_class($originalTable);
        $table = new $class($originalTable->name);
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

    public static function getDriver()
    {
        $config = DB::getConfig(self::$connName);
        return new DbDriver($config);
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