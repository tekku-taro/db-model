<?php
namespace Taro\DBModel\Schema\TableLoading;

use Taro\DBModel\DB\DbManipulator;
use Taro\DBModel\Exceptions\NotFoundException;
use Taro\DBModel\Query\DirectSql;
use Taro\DBModel\Schema\DbDriver;
use Taro\DBModel\Schema\MySql\MySqlTableFetcher;

abstract class TableFetcher
{
    public $name;

    public $encoding;

    /** @var DbDriver */
    protected $driver;

    /** @var DbManipulator $dbManipulator  */
    protected $dbManipulator;

    protected static $connName;

    /** @var array<TableColumnInfo> */
    public $tableColumns;
    /** @var array<TablePrimaryKeyInfo> */
    public $tablePrimaryKey;
    /** @var array<TableForeignKeyInfo> */
    public $tableForeignKeys;
    /** @var array<TableIndexInfo> */
    public $tableIndexes;

    function __construct(string $name, DbDriver $driver, DbManipulator $dbManipulator)
    {
        $this->name = $name;
        $this->driver = $driver;
        $this->dbManipulator = $dbManipulator;
    }

    public static function fetchInfo(string $name, DbDriver $driver, DbManipulator $dbManipulator):self
    {
        switch ($driver->type) {
            case DbDriver::MY_SQL:
                $fetcher = new MySqlTableFetcher($name, $driver, $dbManipulator);
                break;
        }
        
        if(!$fetcher->tableExists($name)) {
            throw new NotFoundException($name . 'テーブルが見つかりません！');
        }
        $fetcher->setTableColumns();
        $fetcher->setTablePrimaryKey();
        $fetcher->setTableForeignKeys();
        $fetcher->setTableIndexes();
        $fetcher->setEncoding();
        return $fetcher;
    }

    protected function tableExists(string $tableName):bool
    {
        $tableNames = [];
        $sql = 'SHOW TABLES';
        $result = DirectSql::query()->prepareSql($sql)->runSql();
        if(is_array($result)) {
            foreach ($result as $key => $row) {
               $tableNames[] = array_values($row)[0];
            }
            return in_array($tableName, $tableNames);
        }
        return false;
    }

    public function setEncoding()
    {
        $sql = $this->getTableEncodingSql();
        $result = $this->execAndGetTableInfo($sql);
        $this->encoding = $result[0]['character_set_name'];
    }

    public function setTableColumns()
    {
        $sql = $this->getTableColumnsSql();
        $result = $this->execAndGetTableInfo($sql);
        $this->hydrateColumnInfo($result);
    }

    public function setTablePrimaryKey()
    {
        $sql = $this->getTablePrimaryKeySql();
        $result = $this->execAndGetTableInfo($sql);
        $this->hydratePrimaryKeyInfo($result);
    }

    public function setTableForeignKeys()
    {
        $sql = $this->getTableForiegnKeySql();
        $result = $this->execAndGetTableInfo($sql);
        $this->hydrateForeignKeysInfo($result);
    }

    public function setTableIndexes()
    {
        $sql = $this->getTableIndexesSql();
        $result = $this->execAndGetTableInfo($sql);
        $this->hydrateIndexesInfo($result);
    }


    /**
     * @param string $sql
     * @return array<array<string>>
     */
    public function execAndGetTableInfo(string $sql):array
    {
        $statement = $this->dbManipulator->executeAndStatement($sql); 
        $results = $statement->fetchAll();
        $statement = null;
        if($results === false) {
            return [];
        }
        return $results;        
    }

    /**
     * @param array<string> $resultSet
     * @return void
     */
    public abstract function hydrateColumnInfo(array $resultSet):void;

    /**
     * @param array<string> $resultSet
     * @return void
     */
    public abstract function hydratePrimaryKeyInfo(array $resultSet):void;
    
    /**
     * @param array<string> $resultSet
     * @return void
     */
    public abstract function hydrateForeignKeysInfo(array $resultSet):void;
    
    /**
     * @param array<string> $resultSet
     * @return void
     */
    public abstract function hydrateIndexesInfo(array $resultSet):void;



    public abstract function getTableColumnsSql():string;

    public abstract function getTableIndexesSql():string;

    public abstract function getTablePrimaryKeySql():string;

    public abstract function getTableEncodingSql():string;
    
    public abstract function getTableForiegnKeySql():string;

}