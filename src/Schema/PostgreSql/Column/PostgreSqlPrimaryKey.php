<?php
namespace Taro\DBModel\Schema\PostgreSql\Column;

use Taro\DBModel\Exceptions\WrongSqlException;
use Taro\DBModel\Schema\Column\PrimaryKey;
use Taro\DBModel\Schema\Table;

class PostgreSqlPrimaryKey extends PrimaryKey
{
    public function compile(): string
    {
        switch ($this->action) {
            case PrimaryKey::ADD_ACTION:
                $sql = $this->generateClause();
                if($this->mode === Table::ALTER_MODE) {
                    return $sql = 'ADD CONSTRAINT ' . $this->generateKeyName() . ' ' . $sql;
                }                  
                break;
            case PrimaryKey::DROP_ACTION:
                if($this->mode === Table::CREATE_MODE) {
                    throw new WrongSqlException('テーブル作成時は、主キー削除クエリは実行できません。');
                }                     
                $sql = 'DROP CONSTRAINT ' . $this->generateKeyName();
                break;
        }
        return $sql;
    }

    protected function generateClause():string
    {   
        return 'PRIMARY KEY ' . ' ( ' . implode(',', $this->columnNames) . ' )';
    }

    private function generateKeyName()
    {
        return $this->tableName . '_pkey';
    }
}