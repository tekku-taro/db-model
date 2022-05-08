<?php
namespace Taro\DBModel\Schema\Sqlite\Column;

use Taro\DBModel\Exceptions\WrongSqlException;
use Taro\DBModel\Schema\Column\ForeignKey;
use Taro\DBModel\Schema\Table;

class SqliteForeignKey extends ForeignKey
{
    public function compile(): string
    {
        switch ($this->action) {
            case ForeignKey::ADD_ACTION:
                $sql = $this->generateClause();
                if($this->mode === Table::ALTER_MODE) {
                    throw new WrongSqlException('sqlite3では、外部キーを追加できません。');
                }
                break;
            case ForeignKey::DROP_ACTION:
                throw new WrongSqlException('sqlite3では、外部キー削除クエリは実行できません。');
                break;
        }
        return $sql;
    }

    protected function generateClause():string
    {
        $clause = 'CONSTRAINT ' . $this->name . ' FOREIGN KEY ( ' . $this->columnName . ' ) ' .
        'REFERENCES ' . $this->referencedTable  . ' ( ' . $this->referencedColumn . ' )';
        if(isset($this->onDelete)) {
            $clause .=  ' ' . 'ON DELETE ' . $this->onDelete;
        }
        if(isset($this->onUpdate)) {
            $clause .= ' ' . 'ON UPDATE ' . $this->onUpdate;
        }
        return $clause;
    }
}