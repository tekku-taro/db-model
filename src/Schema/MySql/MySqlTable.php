<?php
namespace Taro\DBModel\Schema\MySql;

use Taro\DBModel\Schema\Column\Column;
use Taro\DBModel\Schema\Column\ForeignKey;
use Taro\DBModel\Schema\Column\Index;
use Taro\DBModel\Schema\Column\PrimaryKey;
use Taro\DBModel\Schema\MySql\Column\MySqlColumn;
use Taro\DBModel\Schema\MySql\Column\MySqlForeignKey;
use Taro\DBModel\Schema\MySql\Column\MySqlIndex;
use Taro\DBModel\Schema\MySql\Column\MySqlPrimaryKey;
use Taro\DBModel\Schema\Table;

class MySqlTable extends Table
{
    public function addColumn(string $name, string $columnType)
    {
        $column = new MySqlColumn(Column::ADD_ACTION, $name, $columnType);
        $this->columns[] = $column;
    }

    public function changeColumn(string $name,string $newName = null)    
    {
        $this->fetchOriginalColumn($name, Column::CHANGE_ACTION);
    }


    public function dropColumn(string $name)    
    {
        $this->fetchOriginalColumn($name, Column::DROP_ACTION);
    }

    private function fetchOriginalColumn(string $name, string $action)
    {
        $original = $this->original->getColumn($name);
        $column = new MySqlColumn($action, $name, $original->type);
        $column->original = $original;
        $this->columns[] = $column;
    }

    public function addForeign(...$columns)    
    {
        $foreignKey = new MySqlForeignKey(ForeignKey::ADD_ACTION, $columns);
        $this->foreignKeys[] = $foreignKey;
    }

    public function addIndex(...$columns)    
    {
        $index = new MySqlIndex(Index::ADD_ACTION, $columns);
        $this->indexes[] = $index;
    }

    public function dropForeign(string $name)    
    {
        $original = $this->original->getForeign($name);
        $this->fetchOriginalForeign($original, ForeignKey::DROP_ACTION);
    }

    public function dropForeignKeyByColumns(...$columns)    
    {
        $original = $this->original->getForeignByColumns($columns);
        $this->fetchOriginalForeign($original, ForeignKey::DROP_ACTION);
    }

    private function fetchOriginalForeign(ForeignKey $original, string $action)
    {
        $foreignKey = new MySqlForeignKey($action, $original->columnNames);
        $foreignKey->original = $original;
        $this->foreignKeys[] = $foreignKey;
    }



    public function dropIndex(string $name)    
    {
        $original = $this->original->getIndex($name);
        $this->fetchOriginalIndex($original, Index::DROP_ACTION);
    }

    public function dropIndexByColumns(...$columns)    
    {
        $original = $this->original->getIndexByColumns($columns);
        $this->fetchOriginalIndex($original, Index::DROP_ACTION);
    }

    private function fetchOriginalIndex(Index $original, string $action)
    {
        $index = new MySqlIndex($action, $original->columnNames);
        $index->original = $original;
        $this->indexes[] = $index;
    }


    public function addPrimaryKey(...$columns)
    {
        $primaryKey = new MySqlPrimaryKey(PrimaryKey::ADD_ACTION, $columns);
        $this->primaryKey = $primaryKey;
    }


    public function dropPrimaryKey()    
    {
        $original = $this->original->getPrimaryKey();
        $primaryKey = new MySqlPrimaryKey(PrimaryKey::DROP_ACTION);
        $primaryKey->original = $original;
        $this->primaryKey = $primaryKey;
    }

    public function addUnique(...$columns)    
    {
        $index = new MySqlIndex(Index::ADD_ACTION, $columns);
        $index->unique(true);
        $this->indexes[] = $index;
    }

}