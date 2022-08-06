<?php
namespace Taro\DBModel\Schema\PostgreSql\Column;

use Taro\DBModel\Exceptions\WrongSqlException;
use Taro\DBModel\Schema\Column\Index;
use Taro\DBModel\Schema\Table;

class PostgreSqlIndex extends Index
{
    public function compile()
    {
        switch ($this->action) {
            case Index::ADD_ACTION:
                $sql = $this->generateClause();               
                break;
            case Index::DROP_ACTION:
                if($this->mode === Table::CREATE_MODE) {
                    throw new WrongSqlException('テーブル作成時は、インデックス削除クエリは実行できません。');
                }                  
                $sql = 'DROP CONSTRAINT IF EXISTS ' . $this->name . ';';
                $sql .= 'DROP INDEX IF EXISTS ' . $this->name;
                break;
        }
        return $sql;
    }

    public function createIndex()
    {
        if (!$this->unique) {
            return 'CREATE INDEX ' . $this->name . ' ON '. $this->tableName .' ( ' . implode(',', $this->columnNames)  . ' );';
        }
    }
    
    protected function generateClause()
    {
        if($this->unique) {
            $sql = 'CONSTRAINT ' . $this->name . ' UNIQUE ( ' . implode(',', $this->columnNames)  . ' )';
            if($this->mode === Table::ALTER_MODE) {
                $sql = 'ADD ' . $sql;
            }
            return $sql;      
        }     
    }

}