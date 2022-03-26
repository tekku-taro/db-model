<?php
namespace Taro\DBModel\Schema\MySql\Column;

use ErrorException;
use Taro\DBModel\Schema\Column\ForeignKey;
use Taro\DBModel\Schema\Table;

class MySqlForeignKey extends ForeignKey
{
    /**
     * @param string $mode  SET NULL|CASCADE|RESTRICT|NO ACTION
     * @return self
     */
    public function onDelete(string $mode = 'cascade'):self  
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
                $sql = 'DROP FOREIGN KEY ' . $this->fkName;
                break;
        }
        return $sql;
    }

    protected function generateClause():string
    {
        return 'FOREIGN KEY ' . $this->fkName . ' ( ' . implode(',', $this->columnNames) . ' ) ' .
        'REFERENCES ' . $this->referencedTable  . ' ( ' . implode(',', $this->referencedColumns) . ' ) ' .
        'ON DELETE ' . $this->onDelete . 'ON UPDATE ' . $this->onUpdate;
    }
}