<?php
namespace Taro\DBModel\Schema\MySql\Column;

use Taro\DBModel\Schema\Column\Index;

class MySqlIndex extends Index
{
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
                $sql = 'DROP ' . $this->selectIndexOrUnique() . $this->idxName;
                break;
        }

        return $sql;
    }

    
    protected function generateClause():string
    {
        return $this->selectIndexOrUnique() . $this->idxName . ' ( ' . implode(',', $this->columnNames)  . ' ) ';
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