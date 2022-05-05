<?php
namespace Taro\DBModel\Schema\Sqlite\Column;

use Taro\DBModel\Exceptions\WrongSqlException;
use Taro\DBModel\Schema\Column\Index;
use Taro\DBModel\Schema\Table;

class SqliteIndex extends Index
{
    public function compile(): string
    {
        switch ($this->action) {
            case Index::ADD_ACTION:
                $sql = $this->generateClause();
                break;
            case Index::DROP_ACTION:
                if($this->mode === Table::CREATE_MODE) {
                    throw new WrongSqlException('テーブル作成時は、インデックス削除クエリは実行できません。');
                }                  
                $sql = 'DROP INDEX ' . $this->name;
                break;
        }
        return $sql;
    }

    
    protected function generateClause():string
    {
        return $this->selectIndexOrUnique() . $this->name . ' ON ' . $this->tableName . ' ( ' . implode(',', $this->columnNames)  . ' )';
    }

    private function selectIndexOrUnique():string
    {
        if($this->unique) {
            return 'UNIQUE INDEX ';
        } else {
            return 'INDEX ';
        }
    }    
}