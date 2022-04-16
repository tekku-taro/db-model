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
    public function addColumn(string $name, string $columnType):Column
    {
        $column = new MySqlColumn(Column::ADD_ACTION, $name, $columnType, $this->name);
        $this->columns[] = $column;
        return $column;
    }

    public function changeColumn(string $name,string $newName = null):Column 
    {
        $column = $this->fetchOriginalColumn($name, Column::CHANGE_ACTION);
        return $column;
    }


    public function dropColumn(string $name)    
    {
        $this->fetchOriginalColumn($name, Column::DROP_ACTION);
    }

    private function fetchOriginalColumn(string $name, string $action):Column 
    {
        $original = $this->original->getColumn($name);
        $column = new MySqlColumn($action, $name, $original->type, $this->name);
        $column->original = $original;
        $this->columns[] = $column;
        return $column;
    }

    public function addForeign(string $column):ForeignKey
    {
        $foreignKey = new MySqlForeignKey(ForeignKey::ADD_ACTION, $column, $this->name);
        $this->foreignKeys[] = $foreignKey;
        return $foreignKey;
    }

    public function addIndex(...$columns):Index
    {
        $index = new MySqlIndex(Index::ADD_ACTION, $columns, $this->name);
        $this->indexes[] = $index;
        return $index;
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

    private function fetchOriginalForeign(ForeignKey $original, string $action):ForeignKey
    {
        $foreignKey = new MySqlForeignKey($action, $original->columnNames, $this->name);
        $foreignKey->original = $original;
        $this->foreignKeys[] = $foreignKey;
        return $foreignKey;
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

    private function fetchOriginalIndex(Index $original, string $action):Index
    {
        $index = new MySqlIndex($action, $original->columnNames, $this->name);
        $index->original = $original;
        $this->indexes[] = $index;
        return $index;
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
        $index = new MySqlIndex(Index::ADD_ACTION, $columns, $this->name);
        $index->unique(true);
        $this->indexes[] = $index;
    }

    /**
     * @param array<string> $pkColumns
     * @return string
     */
    protected function compilePk(array $columns):string
    {
        if(!empty($columns)) {
            if($this->primaryKey === null) {
                $this->primaryKey = new MySqlPrimaryKey(PrimaryKey::ADD_ACTION, $columns);
            } else {
                $this->primaryKey->addColumns($columns);
            }
        }
        return $this->primaryKey->compile();
    }    

}