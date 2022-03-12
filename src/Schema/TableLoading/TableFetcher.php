<?php
namespace Taro\DBModel\Schema\TableLoading;

use Taro\DBModel\DB\DbManipulator;
use Taro\DBModel\Schema\DbDriver;
use Taro\DBModel\Schema\MySql\MySqlTableFetcher;

abstract class TableFetcher
{
    protected $name;

    /** @var DbDriver */
    protected $driver;

    /** @var DbManipulator $dbManipulator  */
    protected $dbManipulator;

    protected static $connName;

    public $rawData;

    /** @var array<TableColumnInfo> */
    public $tableData;

    function __construct(string $name, DbDriver $driver, DbManipulator $dbManipulator)
    {
        $this->name = $name;
        $this->driver = $driver;
        $this->dbManipulator = $dbManipulator;
    }

    public static function fetchInfo(string $name, DbDriver $driver, DbManipulator $dbManipulator)
    {
        switch ($driver->type) {
            case DbDriver::MY_SQL:
                $fetcher = new MySqlTableFetcher($name, $driver, $dbManipulator);
                break;
        }
        
        $sql = $fetcher->getSchemaInfoSql();
        $fetcher->execAndGetTableInfo($sql);
        return $fetcher->handleInfo();
    }

    public abstract function getSchemaInfoSql():string;

    public abstract function execAndGetTableInfo(string $sql);

    public abstract function handleInfo():array;

}