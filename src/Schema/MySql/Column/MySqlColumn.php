<?php
namespace Taro\DBModel\Schema\MySql\Column;

use Taro\DBModel\Exceptions\NotFoundException;
use Taro\DBModel\Schema\Column\Column;
use Taro\DBModel\Schema\MySql\Column\ColumnType\MySqlColumnTypeMap;
use Taro\DBModel\Schema\Table;

class MySqlColumn extends Column
{
    public function compile(): string
    {
        switch ($this->action) {
            case Column::ADD_ACTION:
                $sql = $this->generateClause();
                if($this->mode === Table::ALTER_MODE) {
                    $sql = 'ADD COLUMN ' . $sql;
                }
                break;
            case Column::CHANGE_ACTION:
                $sql = 'CHANGE COLUMN ' . $this->generateClause();
                break;
            case Column::DROP_ACTION:
                $sql = 'DROP COLUMN ' . $this->name;
                break;
        }

        return $sql;
    }

    private function generateColumnName():string
    {
        $sql = $this->name;
        if($this->action === Column::CHANGE_ACTION) {
            $sql .= ' ' . ($this->rename ?? $this->name);
        }
        return $sql;
    }

    private function generateClause():string
    {
        $sql = $this->generateColumnName() . ' ' . $this->generateType();
        if(!empty($options = $this->generateOptions())) {
            $sql .= ' ' . $options;
        }
        return $sql;
    }

    private function generateType():string
    {
        $block = $this->type;
   
        if(isset($this->length)) {
            $block .= '(' . $this->length . ')';
        }elseif(isset($this->precision)) {
            $block .= '(' . $this->precision . ')';
        }


        return $block;
    }

    private function generateOptions():string
    {
        $options = [];
        if(isset($this->unsigned)) {
            $options[] = 'UNSIGNED';
        }
        if($this->nullable !== true) {
            $options[] = 'NOT NULL';
        }
        if(isset($this->default)) {
            $options[] = 'DEFAULT "' . $this->default . '"';
        }
        if(isset($this->autoIncrement)) {
            $options[] = 'AUTO_INCREMENT';
        }
        if(isset($this->after)) {
            $options[] = 'AFTER ' . $this->after;
        }
        if(isset($this->before)) {
            $options[] = 'BEFORE ' . $this->before;
        }

        return implode(' ', $options);
    }


    public function type(string $typeName):Column
    {
        if(MySqlColumnTypeMap::includes($typeName)) {
            $this->type = MySqlColumnTypeMap::getDBType($typeName);
        }elseif(MySqlColumnTypeMap::isType($typeName)) {
            $this->type = $typeName;
        } else {
            throw new NotFoundException('利用できるカラムのデータ型に' . $typeName . 'というタイプはありません。');
        }
        return $this;
    }    


    public function length(int $number):Column
    {
        if(MySqlColumnTypeMap::checkHasLength($this->typeName)) {
            $this->length = $number;
        } else {
            throw new NotFoundException('データ型:'.$this->type.'は最大文字数を設定できません。');
        }
        return $this;
    }    

    public function precision(int $number):Column
    {
        $this->precision = $number;
        return $this;
    }    
}