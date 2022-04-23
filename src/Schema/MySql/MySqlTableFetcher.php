<?php
namespace Taro\DBModel\Schema\MySql;

use Taro\DBModel\DB\DbManipulator;
use Taro\DBModel\Schema\TableLoading\TableColumnInfo;
use Taro\DBModel\Schema\TableLoading\TableFetcher;
use Taro\DBModel\Schema\TableLoading\TableForeignKeyInfo;
use Taro\DBModel\Schema\TableLoading\TableIndexInfo;
use Taro\DBModel\Schema\TableLoading\TablePrimaryKeyInfo;

class MySqlTableFetcher extends TableFetcher
{

    public function getTableColumnsSql():string
    {
        return 'SELECT *
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_NAME = "'.$this->name.'"
        AND TABLE_SCHEMA = "'.$this->driver->dbName.'"
        ;';
    }

    public function getTableIndexesSql():string
    {
        return 'SELECT * 
        FROM INFORMATION_SCHEMA.STATISTICS 
        WHERE TABLE_NAME = "'.$this->name.'"
        AND TABLE_SCHEMA = "'.$this->driver->dbName.'"
        ;';
    }

    public function getTablePrimaryKeySql():string
    {
        return 'SELECT CONSTRAINT_NAME,TABLE_SCHEMA,TABLE_NAME, k.COLUMN_NAME
        FROM information_schema.table_constraints t
        JOIN information_schema.key_column_usage k
        USING(CONSTRAINT_NAME,TABLE_SCHEMA,TABLE_NAME)
        WHERE t.CONSTRAINT_TYPE="PRIMARY KEY"
        AND t.TABLE_NAME = "'.$this->name.'"
        AND t.TABLE_SCHEMA = "'.$this->driver->dbName.'"
        ;';
    }

    public function getTableEncodingSql():string
    {
        return 'SELECT CCSA.character_set_name 
        FROM information_schema.`TABLES` T,
        information_schema.`COLLATION_CHARACTER_SET_APPLICABILITY` CCSA
        WHERE CCSA.collation_name = T.table_collation
        AND T.TABLE_NAME = "'.$this->name.'"
        AND T.TABLE_SCHEMA = "'.$this->driver->dbName.'"
        ;';
    }

    public function getTableForiegnKeySql():string
    {
        return 'SELECT i.TABLE_NAME, i.CONSTRAINT_TYPE, i.CONSTRAINT_NAME, k.REFERENCED_TABLE_NAME, k.REFERENCED_COLUMN_NAME ,k.COLUMN_NAME
        FROM information_schema.TABLE_CONSTRAINTS i 
        LEFT JOIN information_schema.KEY_COLUMN_USAGE k ON i.CONSTRAINT_NAME = k.CONSTRAINT_NAME 
        WHERE i.CONSTRAINT_TYPE = "FOREIGN KEY" 
        AND i.TABLE_NAME = "'.$this->name.'"
        AND i.TABLE_SCHEMA = "'.$this->driver->dbName.'"
        ;';
    }


    /**
     * @param array<string> $resultSet
     * @return void
     */
    public function hydrateColumnInfo(array $resultSet):void
    {
        $data = [];
        foreach ($resultSet as $row) {
            $tableColumnInfo = new TableColumnInfo;

            $tableColumnInfo->tableName = $row['TABLE_NAME'];
            $tableColumnInfo->name = $row['COLUMN_NAME'];
            $tableColumnInfo->dataType = $row['DATA_TYPE'];
            $tableColumnInfo->unsigned = $this->checkIfExists($row['COLUMN_TYPE'], 'unsigned');
            $tableColumnInfo->numericPrecision = $row['NUMERIC_PRECISION'];
            $tableColumnInfo->maxLength = $row['CHARACTER_MAXIMUM_LENGTH'];
            $tableColumnInfo->isNullable = ($row['IS_NULLABLE'] === 'YES')? true:false;
            $tableColumnInfo->default = $this->getDefaultVal($row['COLUMN_DEFAULT']);
            $tableColumnInfo->autoIncrement = $this->checkIfExists($row['EXTRA'], 'auto_increment');
            $data[] = $tableColumnInfo;
        }

        $this->tableColumns = $data;
    }   

    private function getDefaultVal($rawValue)
    {
        if($rawValue === 'NULL' || $rawValue === null) {
            return null;
        }
        return str_replace('\'','',$rawValue);
    }

    /**
     * @param array<string> $resultSet
     * @return void
     */
    public function hydratePrimaryKeyInfo(array $resultSet):void
    {
        $data = [];
        foreach ($resultSet as $row) {
            $tableInfo = new TablePrimaryKeyInfo;

            $tableInfo->tableName = $row['TABLE_NAME'];
            $tableInfo->name = $row['CONSTRAINT_NAME'];
            $tableInfo->columnName = $row['COLUMN_NAME'];

            $data[] = $tableInfo;
        }

        $this->tablePrimaryKey = $data;
    }   

    /**
     * @param array<string> $resultSet
     * @return void
     */
    public function hydrateForeignKeysInfo(array $resultSet):void
    {
        $data = [];
        foreach ($resultSet as $row) {
            $tableInfo = new TableForeignKeyInfo;
            $tableInfo->tableName = $row['TABLE_NAME'];
            $tableInfo->name = $row['CONSTRAINT_NAME'];
            $tableInfo->columnName = $row['COLUMN_NAME'];
            $tableInfo->referencedColumnName = $row['REFERENCED_COLUMN_NAME'];
            $tableInfo->referencedTable = $row['REFERENCED_TABLE_NAME'];

            $data[] = $tableInfo;
        }

        $this->tableForeignKeys = $data;
    }   

    /**
     * @param array<string> $resultSet
     * @return void
     */
    public function hydrateIndexesInfo(array $resultSet):void
    {
        $data = [];

        foreach ($resultSet as $row) {
            if($row['INDEX_NAME'] === 'PRIMARY') {
                continue;
            }
            $tableInfo = new TableIndexInfo;
            $tableInfo->tableName = $row['TABLE_NAME'];
            $tableInfo->name = $row['INDEX_NAME'];
            $tableInfo->columnName = $row['COLUMN_NAME'];
            $tableInfo->isUnique = ($row['NON_UNIQUE'] == 1) ? false:true;

            $data[] = $tableInfo;
        }

        $this->tableIndexes = $data;
    }   

    public function checkIfExists($value,string $key):bool
    {
        if(strpos($value, $key) !== false) {
            return true;
        }

        return false;
    }
}