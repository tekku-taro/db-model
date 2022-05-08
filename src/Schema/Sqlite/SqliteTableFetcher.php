<?php
namespace Taro\DBModel\Schema\Sqlite;

use Taro\DBModel\Schema\TableLoading\TableColumnInfo;
use Taro\DBModel\Schema\TableLoading\TableFetcher;
use Taro\DBModel\Schema\TableLoading\TableForeignKeyInfo;
use Taro\DBModel\Schema\TableLoading\TableIndexInfo;
use Taro\DBModel\Schema\TableLoading\TablePrimaryKeyInfo;
use Taro\DBModel\Utilities\Str;

class SqliteTableFetcher extends TableFetcher
{

    public function getTableColumnsSql():string
    {
        return 'SELECT * FROM pragma_table_info("'.$this->name.'");';
    }

    public function getTableIndexesSql():string
    {
        return 'SELECT name, sql FROM sqlite_master 
        WHERE tbl_name = "'.$this->name.'"
        AND type = "index"
        ;';
    }

    public function getTablePrimaryKeySql():string
    {
        return 'SELECT l.name FROM pragma_table_info("' . $this->name . '") AS l WHERE l.pk > 0;';
    }

    public function getTableEncodingSql():string
    {
        return 'PRAGMA encoding;';
    }

    public function getTableForiegnKeySql():string
    {
        return $this->getCreateTableSql();
    }

    private function getCreateTableSql()
    {
        return 'SELECT sql FROM sqlite_master WHERE tbl_name = "' . $this->name . '" AND type = "table";';
    }


    public function setEncoding()
    {
        $sql = $this->getTableEncodingSql();
        $result = $this->execAndGetTableInfo($sql);
        $this->encoding = $result;
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

            $tableColumnInfo->tableName = $this->name;
            $tableColumnInfo->name = $row['name'];
            $tableColumnInfo->dataType = strtoupper($row['type']);
            $tableColumnInfo->isNullable = ($row['notnull'] === 0)? true:false;
            $tableColumnInfo->default = $this->getDefaultVal($row['dflt_value']);
            $data[] = $tableColumnInfo;
        }

        $this->tableColumns = $data;
    }   

    private function getDefaultVal($rawValue)
    {
        if($rawValue === 'NULL' || $rawValue === null) {
            return null;
        }
        return str_replace(['\'', '"'],'',$rawValue);
    }

    /**
     * @param array<string> $resultSet
     * @return void
     */
    public function hydratePrimaryKeyInfo(array $resultSet):void
    {
        $data = [];
        foreach ($resultSet as $result) {
            $tableInfo = new TablePrimaryKeyInfo;

            $tableInfo->tableName = $this->name;
            $tableInfo->name = strtoupper($this->name) . '_PK';
            $tableInfo->columnName = $result['name'];

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
        $foreignInfos = $this->parseForForeign($resultSet);

        $data = [];
        foreach ($foreignInfos as $row) {
            $tableInfo = new TableForeignKeyInfo;
            $tableInfo->tableName = $this->name;
            $tableInfo->name = $row['CONSTRAINT_NAME'];
            $tableInfo->columnName = $row['COLUMN_NAME'];
            $tableInfo->referencedColumnName = $row['REFERENCED_COLUMN_NAME'];
            $tableInfo->referencedTable = $row['REFERENCED_TABLE_NAME'];
            $tableInfo->onUpdate = $row['ON_UPDATE'] ?? null;
            $tableInfo->onDelete = $row['ON_DELETE'] ?? null;

            $data[] = $tableInfo;
        }

        $this->tableForeignKeys = $data;
    }   

    private function parseForForeign(array $resultSet):array
    {
        $foreignInfos = [];
        // カンマでsql分離
        $lines = $this->splitSqlByComma($resultSet[0]['sql']);
        // 最初が CONSTRAINT で始まり、 FOREIGN KEY を含む要素を抽出
        $filtered = array_filter($lines, function(string $line) {
            return Str::startWith('CONSTRAINT', $line) && str_contains($line, 'FOREIGN KEY');
        });
        foreach ($filtered as $line) {
            // preg_match で、 name, foreign_key, referencingTable, column を取得
            if(preg_match('/CONSTRAINT (.+) FOREIGN KEY \((.+)\) REFERENCES (.+) \((.+)\)/', $line, $matches)) {
                $foreignInfo = [
                    'CONSTRAINT_NAME'=> trim($matches[1]),
                    'COLUMN_NAME'=> trim($matches[2]),
                    'REFERENCED_TABLE_NAME'=> trim($matches[3]),
                    'REFERENCED_COLUMN_NAME'=> trim($matches[4]),
                ];
                // ON DELETE と ON UPDATE をチェックして、指定値を取得
                if(preg_match('/ON DELETE (\S+)/', $line, $matches)) {
                    $foreignInfo['ON_DELETE'] = strtoupper($matches[1]);
                }
                if(preg_match('/ON UPDATE (\S+)/', $line, $matches)) {
                    $foreignInfo['ON_UPDATE'] = strtoupper($matches[1]);
                }
                $foreignInfos[] = $foreignInfo;
            }
        }
        return $foreignInfos;
    }

    /**
     * @param array<string> $resultSet
     * @return void
     */
    public function hydrateIndexesInfo(array $resultSet):void
    {
        $indexInfos = $this->parseForIndex($resultSet);
        $data = [];

        foreach ($indexInfos as $row) {
            $tableInfo = new TableIndexInfo;
            $tableInfo->tableName = $this->name;
            $tableInfo->name = $row['INDEX_NAME'];
            $tableInfo->columnName = $row['COLUMN_NAME'];
            $tableInfo->isUnique = ($row['NON_UNIQUE'] == 1) ? false:true;

            $data[] = $tableInfo;
        }

        $this->tableIndexes = $data;
    }   

    private function parseForIndex(array $resultSet):array
    {
        $indexInfos = [];
        foreach ($resultSet as $row) {
            if(!isset($row['sql'])) {
                continue;
            }
            if(Str::startWith('CREATE UNIQUE INDEX', $row['sql'])) {
                $nonUnique = false;
            } else {
                $nonUnique = true;
            }
            
            // preg_match で、 columnnames を取得
            preg_match('/ \((.+)\)$/', $row['sql'], $matches);
            $columnNames = explode(',', $matches[1]);
            foreach ($columnNames as $columnName) {
                $indexInfo = [
                    'INDEX_NAME'=> $row['name'],
                    'COLUMN_NAME'=> trim($columnName),
                    'NON_UNIQUE'=> $nonUnique,
                ];
                $indexInfos[] = $indexInfo;                
            }
        }
        return $indexInfos;
    }

    public function setExtra()
    {
        $columnNames = [];
        // autoincrement の取得と設定
        $sql = $this->getCreateTableSql();
        $result = $this->execAndGetTableInfo($sql);
              
        // カンマでsql分離
        $lines = $this->splitSqlByComma($result[0]['sql']);

        foreach ($lines as $line) {
            if (strpos($line, 'autoincrement') !== false) {
                $columnNames[] = array_filter(explode(' ', $line))[0];
            }
        }

        foreach ($this->tableColumns as $column) {
            if(in_array($column, $columnNames)) {
                $column->autoIncrement = true;
            }
        }
    }

    /**
     * @param string $sql
     * @return array<string>
     */
    private function splitSqlByComma(string $sql):array
    {
        // CREATE TABLE test ( id INTEGER NOT NULL,content TEXT,status TEXT NOT NULL DEFAULT "good",user_id INTEGER NOT NULL,PRIMARY KEY  ( id ),CONSTRAINT fk_test_user_id_users_id FOREIGN KEY ( user_id ) REFERENCES users ( id ) ON DELETE CASCADE );        
        if(preg_match('/^CREATE TABLE \w+ \((.+)\)$/', $sql, $matches)) {
            return array_map(function($line) {
                return trim($line);
            }, explode(',', $matches[1]));
        }
        return [];
    }
}