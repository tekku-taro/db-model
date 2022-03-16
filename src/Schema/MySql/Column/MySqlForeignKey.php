<?php
namespace Taro\DBModel\Schema\MySql\Column;

use ErrorException;
use Taro\DBModel\Schema\Column\ForeignKey;

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
        switch ($this->mode) {
            case 'create':
                $sql = $this->generateClause();
                break;
            case 'alter':
                $sql = 'ADD ' . $this->generateClause();
                break;
            case 'drop':
                $sql = 'DROP FOREIGN KEY ' . $this->fkName;
                break;
        }

        return $sql;
    }

    protected function generateClause():string
    {
        return 'FOREIGN KEY ' . $this->fkName . ' ( ' . implode(',', $this->columnNames) . ' ) ' .
        'REFERENCES ' . $this->referencedTable  . ' ( ' . $this->referencedColumn . ' ) ' .
        'ON DELETE ' . $this->onDelete . 'ON UPDATE ' . $this->onUpdate;
    }
}