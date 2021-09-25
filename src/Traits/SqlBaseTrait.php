<?php
namespace Taro\DBModel\Traits;

use Taro\DBModel\Exceptions\WrongSqlException;

trait SqlBaseTrait
{

    public function prepareInsert(array $record):string
    {
        $this->validateSqlBlocks(['record', 'table'], $record);

        $sql = 'INSERT INTO ' . $this->table . ' ';        
        $columns = array_keys($record);
        $values = array_values($record);


        $sql .= '(' . implode(', ', $columns) . ') VALUES (' . implode(', ', $values) . ');';
        return $sql;
    }

    public function prepareBulkInsert(array $recordList):string
    {        
        $this->validateSqlBlocks(['record', 'table'], $recordList);

        $sql = 'INSERT INTO ' . $this->table . ' ';
        $valuesList = [];
        foreach ($recordList as $idx => $record) {
            if($idx === 0) {
                $columns = array_keys($record);
            }

            $valuesList[] = '(' . implode(', ', $record) . ')';
        }

        $sql .= '(' . implode(', ', $columns) . ') VALUES ' . implode(', ', $valuesList) . ';';
        return $sql;
    }

    public function prepareUpdate(array $record):string
    {
        $this->validateSqlBlocks(['record', 'where', 'table'], $record);

        $sql = 'UPDATE ' . $this->table;
        $sql .= $this->compileJoin(). ' SET ';        
        
        $setClause = [];
        foreach ($record as $column => $value) {
            $setClause[] = $column . ' = ' . $value;
        }
        $sql .= implode(', ', $setClause) . ' ';

        return $sql;        
    }

    public function prepareDelete():string
    {
        $this->validateSqlBlocks(['where', 'table']);

        $sql = 'DELETE FROM ' . $this->table . ' ';

        return $sql;         
    }

    private function validateSqlBlocks(array $checkItems, $data = null): bool
    {
        if(in_array('table', $checkItems) && $this->table === null) {            
            throw new WrongSqlException(' テーブル名がありません。 ');
        }
        if(in_array('record', $checkItems) && $data === null) {            
            throw new WrongSqlException(' 保存するデータがありません。 ');
        }
        if(in_array('where', $checkItems) && !isset($this->where)) {            
            throw new WrongSqlException(' WHERE で対象レコードを制限していません。 ');
        }

        return true;
    }

}