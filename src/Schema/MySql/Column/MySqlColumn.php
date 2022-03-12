<?php
namespace Taro\DBModel\Schema\MySql\Column;

use Taro\DBModel\Schema\Column\Column;

class MySqlColumn extends Column
{
    public function compile(): string
    {
        switch ($this->mode) {
            case 'create':
                $sql = $this->name . ' ' . $this->generateType();
                if(empty($options = $this->generateOptions())) {
                    $sql .= ' ' . $options;
                }
                break;
            case 'alter':
                $sql = 'ADD COLUMN ' . $this->name . ' ' . $this->generateType();
                if(empty($options = $this->generateOptions())) {
                    $sql .= ' ' . $options;
                }
                break;
            case 'drop':
                $sql = 'DROP COLUMN ' . $this->name;
                break;
        }

        return $sql;
    }

    private function generateType():string
    {
        $block = $this->type;
        if(isset($this->length)) {
            $block .= '(' . $this->length . ')';
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
            $options[] = 'DEFAULT ' . $this->default;
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
}