<?php
namespace Taro\DBModel\Schema\PostgreSql\Column;

use Taro\DBModel\Exceptions\NotFoundException;
use Taro\DBModel\Exceptions\WrongSqlException;
use Taro\DBModel\Schema\Column\Column;
use Taro\DBModel\Schema\PostgreSql\Column\ColumnType\PostgreSqlColumnTypeMap;
use Taro\DBModel\Schema\Table;

class PostgreSqlColumn extends Column
{

    private $typeModified = false;

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
                if($this->mode === Table::CREATE_MODE) {
                    throw new WrongSqlException('テーブル作成時は、カラム更新クエリは実行できません。');
                }                
                $sql = $this->generateAlterColumns();
                break;
            case Column::DROP_ACTION:
                if($this->mode === Table::CREATE_MODE) {
                    throw new WrongSqlException('テーブル作成時は、カラム削除クエリは実行できません。');
                }                
                $sql = 'DROP COLUMN ' . $this->name;
                break;
        }

        return $sql;
    }

    public function getAlterColumnTypeToSerialSQL()
    {
        if(isset($this->autoIncrement)) {
            $seqName = $this->tableName . '_' . $this->name . '_seq';
            $sql = 'CREATE SEQUENCE ' . $seqName . ';';
            $sql .= 'ALTER TABLE ' . $this->tableName . ' ALTER  ' . $this->name . " SET DEFAULT nextval('" . $seqName . "');";
            $sql .= 'ALTER SEQUENCE ' . $seqName . ' OWNED BY ' . $this->tableName . '.' . $this->name . ';';
            return $sql;
        }        
    }


    private function generateClause():string
    {
        $sql = $this->name . ' ' . $this->generateType();
        if(!empty($options = $this->generateOptions())) {
            $sql .= ' ' . $options;
        }
        return $sql;
    }

    private function generateAlterColumns():string
    {
        $sqls = [];
        if($this->typeModified) {
            $sqls[] = 'ALTER COLUMN ' . $this->name . ' TYPE ' . $this->generateType();
        } 
        if($this->nullable === true) {
            $sqls[] = 'ALTER COLUMN ' . $this->name . ' DROP NOT NULL';
        } elseif($this->nullable === false) {
            $sqls[] = 'ALTER COLUMN ' . $this->name . ' SET NOT NULL';
        }
        if(isset($this->default)) {
            $sqls[] = 'ALTER COLUMN ' . $this->name . " SET DEFAULT '" . $this->default . "'";
        }
        if(isset($this->rename)) {
            $sqls[] = 'RENAME COLUMN ' . $this->name . ' TO ' . $this->rename;
        }

        return implode(',', $sqls);
    }

    private function generateType():string
    {
        if($this->action === Column::ADD_ACTION && isset($this->autoIncrement)) {
            return 'SERIAL';
        }

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
        if($this->nullable !== true) {
            $options[] = 'NOT NULL';
        }
        if(isset($this->default)) {
            $options[] = "DEFAULT '" . $this->default . "'";
        }
        return implode(' ', $options);
    }


    public function type(string $typeName):Column
    {
        if(PostgreSqlColumnTypeMap::includes($typeName)) {
            $this->type = PostgreSqlColumnTypeMap::getDBType($typeName);
        }elseif(PostgreSqlColumnTypeMap::isType($typeName)) {
            $this->type = $typeName;
        } else {
            throw new NotFoundException('利用できるカラムのデータ型に' . $typeName . 'というタイプはありません。');
        }
        if($this->action === Column::CHANGE_ACTION) {
            $this->typeModified = true;
        }        
        $this->setDefaultLength();
        return $this;
    }    


    public function length(int $number):Column
    {
        if(PostgreSqlColumnTypeMap::checkHasLength($this->typeName)) {
            $this->length = $number;
        } else {
            throw new NotFoundException('データ型:'.$this->type.'は最大文字数を設定できません。');
        }
        return $this;
    }    

    protected function setDefaultLength()
    {
        if(PostgreSqlColumnTypeMap::checkHasLength($this->typeName)) {
            $this->length = PostgreSqlColumnTypeMap::DEFAULT_CHAR_LENGTH;
        }
    }

    public function precision(int $number):Column
    {
        $this->precision = $number;
        return $this;
    }    
}