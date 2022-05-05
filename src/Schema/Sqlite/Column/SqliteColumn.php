<?php
namespace Taro\DBModel\Schema\Sqlite\Column;

use Taro\DBModel\Exceptions\NotFoundException;
use Taro\DBModel\Exceptions\WrongSqlException;
use Taro\DBModel\Schema\Column\Column;
use Taro\DBModel\Schema\Sqlite\Column\ColumnType\SqliteColumnTypeMap;
use Taro\DBModel\Schema\Table;

class SqliteColumn extends Column
{
    public const RENAME_ACTION = 'RENAME';

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
                throw new WrongSqlException('sqlite3では、カラム更新クエリは実行できません。');
                break;
            case SqliteColumn::RENAME_ACTION:
                if($this->mode === Table::CREATE_MODE) {
                    throw new WrongSqlException('テーブル作成時は、カラム名変更クエリは実行できません。');
                }                
                $sql = 'RENAME COLUMN ' . $this->name . ' TO ' . $this->rename;
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

    private function generateClause():string
    {
        $sql = $this->name . ' ' . $this->generateType();
        if(!empty($options = $this->generateOptions())) {
            $sql .= ' ' . $options;
        }
        return $sql;
    }

    private function generateType():string
    {
        $block = $this->type;

        return $block;
    }

    private function generateOptions():string
    {
        $options = [];
        if($this->nullable !== true) {
            $options[] = 'NOT NULL';
        }
        if(isset($this->default)) {
            $options[] = 'DEFAULT "' . $this->default . '"';
        }
        if(isset($this->autoIncrement)) {
            $options[] = 'AUTOINCREMENT';
        }

        return implode(' ', $options);
    }


    public function type(string $typeName):Column
    {
        if(SqliteColumnTypeMap::includes($typeName)) {
            $this->type = SqliteColumnTypeMap::getDBType($typeName);
        }elseif(SqliteColumnTypeMap::isType($typeName)) {
            $this->type = $typeName;
        } else {
            throw new NotFoundException('利用できるカラムのデータ型に' . $typeName . 'というタイプはありません。');
        }

        return $this;
    }    

}