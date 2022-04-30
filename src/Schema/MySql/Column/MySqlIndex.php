<?php
namespace Taro\DBModel\Schema\MySql\Column;

use Taro\DBModel\Exceptions\WrongSqlException;
use Taro\DBModel\Schema\Column\Index;
use Taro\DBModel\Schema\Table;

class MySqlIndex extends Index
{
    public function compile(): string
    {
        switch ($this->action) {
            case Index::ADD_ACTION:
                $sql = $this->generateClause();
                if($this->mode === Table::ALTER_MODE) {
                    $sql = 'ADD ' . $sql;
                }
                break;
            case Index::DROP_ACTION:
                if($this->mode === Table::CREATE_MODE) {
                    throw new WrongSqlException('テーブル作成時は、インデックス削除クエリは実行できません。');
                }                  
                $sql = 'DROP ' . $this->selectIndexOrUnique() . $this->name;
                break;
        }
        return $sql;
    }

    
    protected function generateClause():string
    {
        return $this->selectIndexOrUnique() . $this->name . ' ( ' . implode(',', $this->columnNames)  . ' )';
    }

    private function selectIndexOrUnique():string
    {
        if($this->unique) {
            return 'UNIQUE ';
        } else {
            return 'INDEX ';
        }
    }    
}