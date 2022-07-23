<?php
namespace Taro\DBModel\Schema\PostgreSql;


use Taro\DBModel\Schema\Column\Column;
use Taro\DBModel\Schema\Column\ForeignKey;
use Taro\DBModel\Schema\Column\Index;
use Taro\DBModel\Schema\Column\PrimaryKey;
use Taro\DBModel\Schema\Interfaces\IPostgreSqlTable;
use Taro\DBModel\Schema\PostgreSql\Column\PostgreSqlColumn;
use Taro\DBModel\Schema\PostgreSql\Column\PostgreSqlForeignKey;
use Taro\DBModel\Schema\PostgreSql\Column\PostgreSqlIndex;
use Taro\DBModel\Schema\PostgreSql\Column\PostgreSqlPrimaryKey;
use Taro\DBModel\Schema\Table;

class PostgreSqlTable extends Table implements IPostgreSqlTable
{
    public function addColumn(string $name, string $columnType):PostgreSqlColumn
    {
        $column = new PostgreSqlColumn(Column::ADD_ACTION, $name, $columnType, $this->name);
        $this->columns[] = $column;
        return $column;
    }

    public function changeColumn(string $name,string $newName = null):PostgreSqlColumn 
    {
        $column = $this->fetchOriginalColumn($name, Column::CHANGE_ACTION);
        return $column;
    }


    public function dropColumn(string $name)    
    {
        $this->fetchOriginalColumn($name, Column::DROP_ACTION);
    }

    private function fetchOriginalColumn(string $name, string $action):PostgreSqlColumn 
    {
        $original = $this->original->getColumn($name);
        $column = new PostgreSqlColumn($action, $name, $original->typeName, $this->name);
        if($original->length !== null) {
            $column->length($original->length);
        }
        if($original->precision !== null) {
            $column->precision($original->precision);
        }
        $column->original = $original;
        $this->columns[] = $column;
        return $column;
    }

    public function addForeign(string $column):PostgreSqlForeignKey
    {
        $foreignKey = new PostgreSqlForeignKey(ForeignKey::ADD_ACTION, $column, $this->name);
        $this->foreignKeys[] = $foreignKey;
        return $foreignKey;
    }

    public function addIndex(...$columns):PostgreSqlIndex
    {
        $index = new PostgreSqlIndex(Index::ADD_ACTION, $columns, $this->name);
        $this->indexes[] = $index;
        return $index;
    }

    public function dropForeign(string $name)    
    {
        $original = $this->original->getForeign($name);
        $foreignKey = $this->fetchOriginalForeign($original, ForeignKey::DROP_ACTION);
        $this->dropIndex($foreignKey->name);
    }

    public function dropForeignKeyByColumn(string $column)    
    {
        $original = $this->original->getForeignByColumn($column);
        $foreignKey = $this->fetchOriginalForeign($original, ForeignKey::DROP_ACTION);
        $this->dropIndex($foreignKey->name);
    }

    private function fetchOriginalForeign(ForeignKey $original, string $action):PostgreSqlForeignKey
    {
        $foreignKey = new PostgreSqlForeignKey($action, $original->columnName, $this->name);
        $foreignKey->references($original->referencedTable, $original->referencedColumn);
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

    private function fetchOriginalIndex(Index $original, string $action):PostgreSqlIndex
    {
        $index = new PostgreSqlIndex($action, $original->columnNames, $this->name);
        $index->name($original->name);
        $index->original = $original;
        $this->indexes[] = $index;
        return $index;
    }


    public function addPrimaryKey(...$columns)
    {
        $primaryKey = new PostgreSqlPrimaryKey(PrimaryKey::ADD_ACTION, $columns, $this->name);
        $this->primaryKey = $primaryKey;
    }


    public function dropPrimaryKey()    
    {
        $original = $this->original->getPrimaryKey();
        $primaryKey = new PostgreSqlPrimaryKey(PrimaryKey::DROP_ACTION, [], $this->name);
        $primaryKey->original = $original;
        $this->primaryKeyToBeDropped = $primaryKey;
    }

    public function addUnique(...$columns)    
    {
        $index = new PostgreSqlIndex(Index::ADD_ACTION, $columns, $this->name);
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
                $this->primaryKey = new PostgreSqlPrimaryKey(PrimaryKey::ADD_ACTION, $columns, $this->name);
            } else {
                $this->primaryKey->addColumns($columns);
            }
        }
        return $this->primaryKey->compile();
    }   
    
    protected function addAdditonalSqls(string $mode, string $sql):string
    {
        if($mode === self::ALTER_MODE) {
            foreach ($this->columns as $column) {
                /** @var PostgreSqlColumn $column */
                if($column->action === Column::CHANGE_ACTION && $column->autoIncrement) {
                    $sql .= $column->getAlterColumnTypeToSerialSQL();
                }            
            }
        }        

        foreach ($this->indexes as $index) {
            /** @var PostgreSqlIndex $index */
            if ($index->action === Index::ADD_ACTION) {
                $indexSql = $index->createIndex();
                if($indexSql !== null) {
                    $sql .= $indexSql;
                }
            }
        }

        return $sql;
    }    

}