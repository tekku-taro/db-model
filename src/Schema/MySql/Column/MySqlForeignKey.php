<?php
namespace Taro\DBModel\Schema\MySql\Column;

use ErrorException;
use Taro\DBModel\Exceptions\WrongSqlException;
use Taro\DBModel\Schema\Column\ForeignKey;
use Taro\DBModel\Schema\Table;

class MySqlForeignKey extends ForeignKey
{
    /**
     * @param string $mode  SET NULL|CASCADE|RESTRICT|NO ACTION
     * @return self
     */
    public function onDelete(string $mode = 'CASCADE'):ForeignKey  
    {
        if(strtoupper($mode) === 'SET DEFAULT') {
            throw new ErrorException('MySqlでは、SET DEFAULT は使用できません。');
        }

        return parent::onDelete($mode);
    }

    public function compile(): string
    {
        switch ($this->action) {
            case ForeignKey::ADD_ACTION:
                $sql = $this->generateClause();
                if($this->mode === Table::ALTER_MODE) {
                    $sql = 'ADD ' . $sql;
                }
                break;
            case ForeignKey::DROP_ACTION:
                if($this->mode === Table::CREATE_MODE) {
                    throw new WrongSqlException('テーブル作成時は、外部キー削除クエリは実行できません。');
                }                  
                $sql = 'DROP FOREIGN KEY ' . $this->name;
                break;
        }
        return $sql;
    }

    protected function generateClause():string
    {
        $clause = 'FOREIGN KEY ' . $this->name . ' ( ' . $this->columnName . ' ) ' .
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