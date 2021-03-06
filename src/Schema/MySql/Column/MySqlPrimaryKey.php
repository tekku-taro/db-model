<?php
namespace Taro\DBModel\Schema\MySql\Column;

use Taro\DBModel\Exceptions\WrongSqlException;
use Taro\DBModel\Schema\Column\PrimaryKey;
use Taro\DBModel\Schema\Table;

class MySqlPrimaryKey extends PrimaryKey
{
    public function compile(): string
    {
        switch ($this->action) {
            case PrimaryKey::ADD_ACTION:
                $sql = $this->generateClause();
                if($this->mode === Table::ALTER_MODE) {
                    $sql = 'ADD ' . $sql;
                }
                break;
            case PrimaryKey::DROP_ACTION:
                if($this->mode === Table::CREATE_MODE) {
                    throw new WrongSqlException('テーブル作成時は、主キー削除クエリは実行できません。');
                }                     
                $sql = 'DROP PRIMARY KEY';
                break;
        }
        return $sql;
    }

    protected function generateClause():string
    {
        return 'PRIMARY KEY ' . ' ( ' . implode(',', $this->columnNames) . ' )';
    }
}