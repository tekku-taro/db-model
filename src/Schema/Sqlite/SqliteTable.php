<?php
namespace Taro\DBModel\Schema\Sqlite;

use Taro\DBModel\Exceptions\WrongSqlException;
use Taro\DBModel\Schema\Column\Column;
use Taro\DBModel\Schema\Column\ForeignKey;
use Taro\DBModel\Schema\Column\Index;
use Taro\DBModel\Schema\Column\PrimaryKey;
use Taro\DBModel\Schema\Interfaces\ISqliteTable;
use Taro\DBModel\Schema\Sqlite\Column\SqliteColumn;
use Taro\DBModel\Schema\Sqlite\Column\SqliteForeignKey;
use Taro\DBModel\Schema\Sqlite\Column\SqliteIndex;
use Taro\DBModel\Schema\Sqlite\Column\SqlitePrimaryKey;
use Taro\DBModel\Schema\Table;

class SqliteTable extends Table implements ISqliteTable
{

    
    /** @var SqliteTable|null */
    public $createTableForUpdate = null;

    private function getCreateTableForUpdate():SqliteTable
    {
        if($this->original === null) {
            return null;
        }
        if($this->createTableForUpdate === null) {
            $this->createTableForUpdate = clone $this->original;
        }
        return $this->createTableForUpdate;
    }


    public function addColumn(string $name, string $columnType):SqliteColumn
    {
        $column = new SqliteColumn(Column::ADD_ACTION, $name, $columnType, $this->name);
        $this->columns[] = $column;
        return $column;
    }

    public function changeColumn(string $name,string $newName = null):SqliteColumn 
    {
        // create table で更新カラムを追加する
        $column = $this->fetchOriginalColumn($name, Column::ADD_ACTION);
        $createTableForUpdate = $this->getCreateTableForUpdate();
        $createTableForUpdate->replaceColumn($column);
        return $column;
    }

    private function replaceColumn(SqliteColumn $replacingColumn)
    {
        foreach ($this->columns as $idx => $column) {
            if($column->name === $replacingColumn->name) {
                $this->columns[$idx] = $replacingColumn;
            }
        } 

    }

    private function removeColumn(SqliteColumn $replacingColumn)
    {
        foreach ($this->columns as $idx => $column) {
            if($column->name === $replacingColumn->name) {
                unset($this->columns[$idx]);
            }
        } 

    }

    public function dropColumn(string $name)    
    {
        $column = $this->fetchOriginalColumn($name, Column::DROP_ACTION);
        $this->columns[] = $column;
    }

    private function fetchOriginalColumn(string $name, string $action):SqliteColumn 
    {
        $original = $this->original->getColumn($name);
        $column = new SqliteColumn($action, $name, $original->typeName, $this->name);

        $column->original = $original;
        return $column;
    }

    public function addForeign(string $column):SqliteForeignKey
    {
        $foreignKey = new SqliteForeignKey(ForeignKey::ADD_ACTION, $column, $this->name);
        $this->foreignKeys[] = $foreignKey;
        return $foreignKey;
    }

    public function addIndex(...$columns):SqliteIndex
    {
        $index = new SqliteIndex(Index::ADD_ACTION, $columns, $this->name);
        $this->indexes[] = $index;
        return $index;
    }

    public function dropForeign(string $name)    
    {
        $original = $this->original->getForeign($name);
        $this->fetchOriginalForeign($original, ForeignKey::DROP_ACTION);
    }

    public function dropForeignKeyByColumn(string $column)    
    {
        $original = $this->original->getForeignByColumn($column);
        $this->fetchOriginalForeign($original, ForeignKey::DROP_ACTION);
    }

    private function fetchOriginalForeign(ForeignKey $original, string $action):SqliteForeignKey
    {
        $foreignKey = new SqliteForeignKey($action, $original->columnName, $this->name);
        $foreignKey->references($original->referencedTable, $original->referencedColumn);
        $foreignKey->original = $original;
        $createTableForUpdate = $this->getCreateTableForUpdate();
        $createTableForUpdate->foreignKeys[] = $foreignKey;        
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

    private function fetchOriginalIndex(Index $original, string $action):SqliteIndex
    {
        $index = new SqliteIndex($action, $original->columnNames, $this->name);
        $index->name($original->name);
        $index->original = $original;
        $this->indexes[] = $index;
        return $index;
    }


    public function addPrimaryKey(...$columns)
    {
        $primaryKey = new SqlitePrimaryKey(PrimaryKey::ADD_ACTION, $columns, $this->name);
        $this->primaryKey = $primaryKey;
    }


    public function getOrCreatePrimaryKey()
    {
        if(!isset($this->primaryKey)) {
            $this->primaryKey = new SqlitePrimaryKey(PrimaryKey::ADD_ACTION,[], $this->name);
        }
        return $this->primaryKey;
    }    

    public function dropPrimaryKey()    
    {
        $original = $this->original->getPrimaryKey();
        $primaryKey = new SqlitePrimaryKey(PrimaryKey::DROP_ACTION, [], $this->name);
        $primaryKey->original = $original;
        $createTableForUpdate = $this->getCreateTableForUpdate();
        $createTableForUpdate->primaryKey = null;
    }

    public function addUnique(...$columns)    
    {
        $index = new SqliteIndex(Index::ADD_ACTION, $columns, $this->name);
        $index->unique(true);
        $this->indexes[] = $index;
    }

    /**
     * @param array<string> $column
     * @return string
     */
    protected function compilePk(array $column):string
    {
        if(!$this->pkIsSetup) {
            $this->setUpPk($column);
        }
        return $this->primaryKey->compile();
    }    

    protected function setUpPk(array $columns):void
    {
        if(!empty($columns)) {
            if($this->primaryKey === null) {
                $this->primaryKey = new SqlitePrimaryKey(PrimaryKey::ADD_ACTION, $columns, $this->name);
            } else {
                $this->primaryKey->addColumns($columns);
            }
        }
        $this->pkIsSetup = true;
    }

    protected function getCreateTableAllSql(string $mode):string
    {
        return $this->getCreateTableSql($mode) . $this->getIndexSql($mode);   
    }

    protected function getCreateTableSql(string $mode):string
    {
        $createSql = 'CREATE TABLE ' . $this->name . ' ( ';

        $pkColumns = [];
        $columnSql = [];
        $foreignSql = [];
        foreach ($this->columns as $column) {
            $column->mode($mode);
            $columnSql[] = $column->compile();
            if($column->isPk) {
                $pkColumns[] = $column->name;
            }
        }
        if(!empty($columnSql)) {
            $createSql .= implode(',', $columnSql);
        }


        if(isset($this->primaryKey) || !empty($pkColumns)) {
            $createSql .= ',' .  $this->compilePk($pkColumns); 
        }

        foreach ($this->foreignKeys as $foreignKey) {
            if($foreignKey->action === ForeignKey::ADD_ACTION) {
                $foreignKey->mode($mode);
                $foreignSql[] = $foreignKey->compile();
            }
        }
        if(!empty($foreignSql)) {
            $createSql .= ',' . implode(',', $foreignSql);
        }


        $createSql .= ' );';    
        
        return $createSql;
    }

    private function getIndexSql(string $mode)
    {
        $indexSql = '';
        foreach ($this->indexes as $index) {
            $index->mode($mode);
            $indexSql .= $index->compile() . ';';
        }        
        
        return $indexSql;        
    }

    protected function prepareForGenerate()
    {
        foreach ($this->foreignKeys as $foreignKey) {
            $this->getCreateTableForUpdate()->foreignKeys[] = $foreignKey;
            if($foreignKey->action === ForeignKey::ADD_ACTION) {
                foreach ($this->columns as $idx => $column) {
                    if($column->action === Column::ADD_ACTION && $foreignKey->columnName === $column->name) {
                        throw new WrongSqlException('sqlite3で外部キーを追加する場合は、先に対象カラムを作成してください。同時に作成はできません。');
                    }
                }
            }
        }
        $pkColumnNames = [];
        $removingColumnNames = [];
        foreach ($this->columns as $idx => $column) {
            if($column->isPk) {
                $pkColumnNames[] = $column->name;
            }     
            if ($column->action === Column::DROP_ACTION) {
                $removingColumnNames[] = $column->name;
                $this->getCreateTableForUpdate()->removeColumn($column);
                unset($this->columns[$idx]);
            }
        }

        // sqlitetable, createTableForUpdate のインデックスのうち、削除したカラム対象のものを除く
        if(!empty($removingColumnNames)) {
            foreach ($this->getCreateTableForUpdate()->indexes as $idx => $index) {
                if(!empty(array_intersect($removingColumnNames, $index->columnNames))) {
                    unset($this->getCreateTableForUpdate()->indexes[$idx]);
                }
            }
            foreach ($this->indexes as $idx => $index) {
                if(!empty(array_intersect($removingColumnNames, $index->columnNames))) {
                    unset($this->indexes[$idx]);
                }
            }
        }

        if(!empty($pkColumns)) {
            $this->getOrCreatePrimaryKey()->addColumns($pkColumnNames);
        }
        if(isset($this->primaryKey)) {
            $this->getCreateTableForUpdate()->primaryKey = $this->primaryKey;
        }
    }

    protected function getAlterTableSql(string $mode):string
    {
        $sql = '';
        if($this->createTableForUpdate !== null) {
            $sql = 'PRAGMA foreign_keys=off;'.
            'BEGIN TRANSACTION;'.
            'ALTER TABLE ' . $this->createTableForUpdate->name . ' RENAME TO ___old_' . $this->createTableForUpdate->name . ';';

            $sql .= $this->createTableForUpdate->getCreateTableSql(Table::CREATE_MODE);
            
            $columns = $this->createTableForUpdate->getRemainingColumnNames();
            $sql .= 'INSERT INTO ' . $this->createTableForUpdate->name . 
            ' SELECT ' . implode(',', $columns) . ' FROM ___old_' . $this->createTableForUpdate->name . ';' .
            'DROP TABLE ___old_' . $this->createTableForUpdate->name . ';';
        
            $sql .= $this->createTableForUpdate->getIndexSql(Table::CREATE_MODE);
        }


        $baseSql = 'ALTER TABLE ' . $this->name . ' ';

        foreach ($this->indexes as $index) {
            if ($index->action === Index::DROP_ACTION) {
                $index->mode($mode);
                $sql .= $index->compile() . ';';
            }
        }             

        foreach ($this->columns as $column) {
            $column->mode($mode);
            $sql .= $baseSql . $column->compile() . ';';     
        }


        foreach ($this->indexes as $index) {
            if ($index->action === Index::ADD_ACTION) {
                $index->mode($mode);
                $sql .= $index->compile() . ';';
            }
        }

       

        if($this->createTableForUpdate !== null) {
            $sql .= 'COMMIT;'.
            'PRAGMA foreign_keys=on;';
        }

        return $sql;
    }


    protected function validate()
    {
        $pkColumns = $this->getPkColumns();
        foreach ($this->columns as $column) {
            // Columnが isPkなのにnullable設定は不可
            if(in_array($column->name, $pkColumns) && $column->nullable) {
                throw new WrongSqlException($column->name . '主キーカラムはnullable設定はできません。');
            }
        }
    } 
    


    protected function getPkColumns()
    {
        $pkColumns = [];
        if(isset($this->original)) {// テーブル更新
            if(isset($this->createTableForUpdate->primaryKey)) { // pk を変更
                $pkColumns = $this->createTableForUpdate->primaryKey->columnNames;
            } elseif(isset($this->original->primaryKey))  { // pk そのまま
                $pkColumns = $this->original->primaryKey->columnNames;                
            }
        }elseif(isset($this->primaryKey)) { // テーブル新規作成
            $pkColumns = array_merge($pkColumns, $this->primaryKey->columnNames);
        }
        return array_unique($pkColumns);
    }    

}