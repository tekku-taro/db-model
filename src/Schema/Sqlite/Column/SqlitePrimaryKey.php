<?php
namespace Taro\DBModel\Schema\Sqlite\Column;

use Taro\DBModel\Exceptions\WrongSqlException;
use Taro\DBModel\Schema\Column\PrimaryKey;
use Taro\DBModel\Schema\Table;

class SqlitePrimaryKey extends PrimaryKey
{
    public function compile(): string
    {
        switch ($this->action) {
            case PrimaryKey::ADD_ACTION:
                $sql = $this->generateClause();
                if($this->mode === Table::ALTER_MODE) {
                    throw new WrongSqlException('sqlite3では、主キーを追加できません。');
                }
                break;
            case PrimaryKey::DROP_ACTION:
                    throw new WrongSqlException('sqlite3では、主キー削除クエリは実行できません。');
                break;
        }
        return $sql;
    }

    protected function generateClause():string
    {
        return 'PRIMARY KEY ' . ' ( ' . implode(',', $this->columnNames) . ' )';
    }
}