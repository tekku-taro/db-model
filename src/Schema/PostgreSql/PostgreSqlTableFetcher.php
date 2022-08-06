<?php
namespace Taro\DBModel\Schema\PostgreSql;

use Taro\DBModel\DB\DbManipulator;
use Taro\DBModel\Schema\TableLoading\TableColumnInfo;
use Taro\DBModel\Schema\TableLoading\TableFetcher;
use Taro\DBModel\Schema\TableLoading\TableForeignKeyInfo;
use Taro\DBModel\Schema\TableLoading\TableIndexInfo;
use Taro\DBModel\Schema\TableLoading\TablePrimaryKeyInfo;

class PostgreSqlTableFetcher extends TableFetcher
{

    public function getTableColumnsSql():string
    {
        return "SELECT *
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_NAME = '".$this->name."'
        AND TABLE_CATALOG = '".$this->driver->dbName."'
        ;";
    }

    public function getTableIndexesSql():string
    {
        return "select
        pgi.indisunique as IS_UNIQUE,
        t.relname as TABLE_NAME,
        i.relname as INDEX_NAME,
        a.attname as COLUMN_NAME
        from
            pg_index pgi,
            pg_class t,
            pg_class i,
            pg_index ix,
            pg_attribute a
        where
            i.oid = pgi.indexrelid
            and t.oid = ix.indrelid
            and i.oid = ix.indexrelid
            and a.attrelid = t.oid
            and a.attnum = ANY(ix.indkey)
            and t.relkind = 'r'
            and t.relname = '".$this->name."'
        order by
            t.relname,
            i.relname;";
    }

    public function getTablePrimaryKeySql():string
    {
        return "SELECT CONSTRAINT_NAME,TABLE_SCHEMA,TABLE_NAME, k.COLUMN_NAME
        FROM information_schema.table_constraints t
        JOIN information_schema.key_column_usage k
        USING(CONSTRAINT_NAME,TABLE_SCHEMA,TABLE_NAME)
        WHERE t.CONSTRAINT_TYPE='PRIMARY KEY'
        AND t.TABLE_NAME = '".$this->name."'
        AND t.TABLE_CATALOG = '".$this->driver->dbName."'
        ;";
    }

    public function getTableEncodingSql():string
    {
        return "SELECT datcollate AS collation
        FROM pg_database 
        WHERE datname = '".$this->driver->dbName."';";
    }

    public function getTableForiegnKeySql():string
    {
        return "SELECT tc.TABLE_NAME, tc.CONSTRAINT_TYPE, tc.CONSTRAINT_NAME,
        k.column_name,
        ccu.table_name as REFERENCED_TABLE_NAME,
        ccu.column_name as REFERENCED_COLUMN_NAME
        FROM information_schema.TABLE_CONSTRAINTS tc 
        LEFT JOIN information_schema.KEY_COLUMN_USAGE k ON tc.CONSTRAINT_NAME = k.CONSTRAINT_NAME 
        LEFT JOIN information_schema.referential_constraints rc
        ON tc.constraint_catalog = rc.constraint_catalog
        AND tc.constraint_schema = rc.constraint_schema
        AND tc.constraint_name = rc.constraint_name
        LEFT JOIN information_schema.constraint_column_usage ccu
        ON rc.unique_constraint_catalog = ccu.constraint_catalog
        AND rc.unique_constraint_schema = ccu.constraint_schema
        AND rc.unique_constraint_name = ccu.constraint_name		
        WHERE 
        tc.CONSTRAINT_TYPE='FOREIGN KEY' 
        AND tc.TABLE_NAME = '".$this->name."'
        AND tc.TABLE_CATALOG = '".$this->driver->dbName."';"
        ;
    }


    public function setEncoding()
    {
        $sql = $this->getTableEncodingSql();
        $result = $this->execAndGetTableInfo($sql);
        $this->encoding = $result[0]['collation'];
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

            $tableColumnInfo->tableName = $row['table_name'];
            $tableColumnInfo->name = $row['column_name'];
            $tableColumnInfo->dataType = $row['data_type'];
            $tableColumnInfo->numericPrecision = $row['numeric_precision'];
            $tableColumnInfo->maxLength = $row['character_maximum_length'];
            $tableColumnInfo->isNullable = ($row['is_nullable'] === 'YES')? true:false;
            $tableColumnInfo->default = $this->getDefaultVal($row['column_default']);
            $tableColumnInfo->autoIncrement = $this->checkIfExists($row['column_default'], 'nextval');
            $data[] = $tableColumnInfo;
        }

        $this->tableColumns = $data;
    }   

    private function getDefaultVal($rawValue)
    {
        if($this->checkIfExists($rawValue, 'null')) {
            return null;
        }
        if($rawValue === null) {
            return null;
        }
        if(is_numeric($rawValue)) {
            return $rawValue;
        }
        // "nextval('customers_id_seq'::regclass)"
        if($this->checkIfExists($rawValue, 'nextval')) {
            return null;
        }
        // "'hoge'::character varying"
        return str_replace('\'','',explode('::', $rawValue)[0]);
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

            $tableInfo->tableName = $row['table_name'];
            $tableInfo->name = $row['constraint_name'];
            $tableInfo->columnName = $row['column_name'];

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
            $tableInfo->tableName = $row['table_name'];
            $tableInfo->name = $row['constraint_name'];
            $tableInfo->columnName = $row['column_name'];
            $tableInfo->referencedColumnName = $row['referenced_column_name'];
            $tableInfo->referencedTable = $row['referenced_table_name'];

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
            $tableInfo = new TableIndexInfo;
            $tableInfo->tableName = $row['table_name'];
            $tableInfo->name = $row['index_name'];
            $tableInfo->columnName = $row['column_name'];
            $tableInfo->isUnique = $row['is_unique'];

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