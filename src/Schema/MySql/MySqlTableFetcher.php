<?php
namespace Taro\DBModel\Schema\MySql;

use Taro\DBModel\DB\DbManipulator;
use Taro\DBModel\Schema\TableLoading\TableColumnInfo;
use Taro\DBModel\Schema\TableLoading\TableFetcher;

class MySqlTableFetcher extends TableFetcher
{
    public function getSchemaInfoSql():string
    {
        return 'SELECT *
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE table_name = "'.$this->name.'"
        and table_schema = "'.$this->driver->dbName.'"
        ;';
    }

    public function execAndGetTableInfo(string $sql):void
    {
        $statement = $this->dbManipulator->executeAndStatement($sql); 
        $results = $statement->fetchAll();
        $statement = null;
        if($results === false) {
            $this->rawData = [];
        }
        $this->rawData = $results;        
    }

    /**
     * @return array<TableColumnInfo>
     */
    public function handleInfo():array
    {
        $tableData = [];
        foreach ($this->rawData as $columnInfoArray) {
            $tableColumnInfo = new TableColumnInfo;

            $tableColumnInfo->tableName = $columnInfoArray['TABLE_NAME'];
            $tableColumnInfo->name = $columnInfoArray['COLUMN_NAME'];
            $tableColumnInfo->pk = $this->checkIfExists($columnInfoArray['COLUMN_KEY'], 'PRI');
            $tableColumnInfo->dataType = $columnInfoArray['DATA_TYPE'];
            $tableColumnInfo->maxLength = $columnInfoArray['CHARACTER_MAXIMUM_LENGTH'];
            $tableColumnInfo->isNullable = ($columnInfoArray['IS_NULLABLE'] === 'YES')? true:false;
            $tableColumnInfo->default = $columnInfoArray['COLUMN_DEFAULT'];
            $tableColumnInfo->autoIncrement = $this->checkIfExists($columnInfoArray['EXTRA'], 'auto_increment');
            $tableColumnInfo->fk = $this->checkIfExists($columnInfoArray['COLUMN_KEY'], 'MUL');
            $tableColumnInfo->uk = $this->checkIfExists($columnInfoArray['COLUMN_KEY'], 'UNI');
            // TODO
            // $tableColumnInfo->index = $columnInfoArray[''];
            $tableData[] = $tableColumnInfo;
        }

        return $this->tableData = $tableData;
    }   

    public function checkIfExists($value,string $key):bool
    {
        if(strpos($value, $key) !== false) {
            return true;
        }

        return false;
    }
}