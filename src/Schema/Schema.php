<?php
namespace Taro\DBModel\Schema;

use Taro\DBModel\DB\DB;
use Taro\DBModel\DB\DbManipulator;
use Taro\DBModel\Schema\TableLoading\TableFetcher;
use Taro\DBModel\Schema\TableLoading\TableLoader;

class Schema
{
    private static $connName;

    private static function useDB(DbManipulator $dbManipulator):void
    {
        $config = DB::getConfig(self::$connName);      
        if(isset($config['dbname'])) {
            $dbManipulator->exec('USE ' . $config['dbname'] . ';');
        }
    }

    public static function createTable(string $name, Callable $callback):Table
    {        
        $dbManipulator = self::getDbManipulator();
        self::useDB($dbManipulator);
        $config = DB::getConfig(self::$connName);
        $driver = new DbDriver($config['driver'],$config['dbname'] ?? null);
        $table = SchemaFactory::newTable($name, $driver);
        $callback($table);
        $sql = $table->generateSql(Table::CREATE_MODE);
    
        $dbManipulator->exec($sql);        
        return self::getTable($table->name);
    }

    public static function getTable(string $name):Table
    {
        $config = DB::getConfig(self::$connName);
        $driver = new DbDriver($config['driver'],$config['dbname'] ?? null);
        return self::loadTableInfo($name, $driver);
    }

    public static function alterTable(Table $table):Table   
    {
        $dbManipulator = self::getDbManipulator();
        self::useDB($dbManipulator);
        $sql = $table->generateSql(Table::ALTER_MODE);
        $dbManipulator->exec($sql); 
        return self::getTable($table->name);
    }

    public static function dropTable(Table $table)    
    {
        $dbManipulator = self::getDbManipulator();
        self::useDB($dbManipulator);
        $sql = $table->generateSql(Table::DROP_MODE);
        return $dbManipulator->exec($sql); 
    }

    public static function dropTableIfExists(string $name)    
    {
        $dbManipulator = self::getDbManipulator();
        self::useDB($dbManipulator);
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

    public static function setConnection(string $connectionName)
    {
        self::$connName = $connectionName;
    }

    public static function defaultConnection()
    {
        self::$connName = null;
    }

}