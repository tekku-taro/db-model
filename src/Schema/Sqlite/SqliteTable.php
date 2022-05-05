<?php
namespace Taro\DBModel\Schema\Sqlite;

use Taro\DBModel\Exceptions\WrongSqlException;
use Taro\DBModel\Schema\Column\Column;
use Taro\DBModel\Schema\Column\ForeignKey;
use Taro\DBModel\Schema\Column\Index;
use Taro\DBModel\Schema\Column\PrimaryKey;
use Taro\DBModel\Schema\Sqlite\Column\SqliteColumn;
use Taro\DBModel\Schema\Sqlite\Column\SqliteForeignKey;
use Taro\DBModel\Schema\Sqlite\Column\SqliteIndex;
use Taro\DBModel\Schema\Sqlite\Column\SqlitePrimaryKey;
use Taro\DBModel\Schema\Table;

class SqliteTable extends Table
{

    
    /** @var Table|null */
    public $createTableForUpdate;

    private function getCreateTableForUpdate():Table
    {
        if($this->createTableForUpdate === null) {
            $this->createTableForUpdate = unserialize(serialize($this->original));
        }
        return $this->createTableForUpdate;
    }


    public function addColumn(string $name, string $columnType):Column
    {
        $column = new SqliteColumn(Column::ADD_ACTION, $name, $columnType, $this->name);
        $this->columns[] = $column;
        return $column;
    }

    public function changeColumn(string $name,string $newName = null):Column 
    {
        $column = $this->fetchOriginalColumn($name, Column::CHANGE_ACTION);
        $createTableForUpdate = $this->getCreateTableForUpdate();
        $createTableForUpdate->columns[] = $column;
        return $column;
    }


    public function dropColumn(string $name)    
    {
        $column = $this->fetchOriginalColumn($name, Column::DROP_ACTION);
        $this->columns[] = $column;
    }

    private function fetchOriginalColumn(string $name, string $action):Column 
    {
        $original = $this->original->getColumn($name);
        $column = new SqliteColumn($action, $name, $original->typeName, $this->name);

        $column->original = $original;
        return $column;
    }

    public function addForeign(string $column):ForeignKey
    {
        $foreignKey = new SqliteForeignKey(ForeignKey::ADD_ACTION, $column, $this->name);
        $this->foreignKeys[] = $foreignKey;
        return $foreignKey;
    }

    public function addIndex(...$columns):Index
    {
        $index = new SqliteIndex(Index::ADD_ACTION, $columns, $this->name);
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

    private function fetchOriginalForeign(ForeignKey $original, string $action):ForeignKey
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

    private function fetchOriginalIndex(Index $original, string $action):Index
    {
        $index = new SqliteIndex($action, $original->columnNames, $this->name);
        $index->name($original->name);
        $index->original = $original;
        $this->indexes[] = $index;
        return $index;
    }


    public function addPrimaryKey(...$columns)
    {
        $primaryKey = new SqlitePrimaryKey(PrimaryKey::ADD_ACTION, $columns);
        $this->primaryKey = $primaryKey;
    }


    public function dropPrimaryKey()    
    {
        $original = $this->original->getPrimaryKey();
        $primaryKey = new SqlitePrimaryKey(PrimaryKey::DROP_ACTION);
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
     * @param array<string> $pkColumns
     * @return string
     */
    protected function compilePk(array $columns):string
    {
        if(!empty($columns)) {
            if($this->primaryKey === null) {
                $this->primaryKey = new SqlitePrimaryKey(PrimaryKey::ADD_ACTION, $columns);
            } else {
                $this->primaryKey->addColumns($columns);
            }
        }
        return $this->primaryKey->compile();
    }    

    protected function prepareForGenerate()
    {
        foreach ($this->foreignKeys as $foreignKey) {
            $this->createTableForUpdate->foreignKeys[] = $foreignKey;
        }
        
        $pkColumns = array_filter(array_map(function(Column $column){
            if($column->isPk) {
                return $column->name;
            }
        }, $this->columns));

        if(!empty($pkColumns)) {
            $this->primaryKey->addColumns($pkColumns);
        }
        if(isset($this->primaryKey)) {
            $this->createTableForUpdate->primaryKey = $this->primaryKey;
        }
    }

    protected function getAlterTableSql(string $mode):string
    {
        $sql = '';
        if($this->createTableForUpdate !== null) {
            $sql = '
            PRAGMA foreign_keys=off;
            BEGIN TRANSACTION;            
            ALTER TABLE ' . $this->createTableForUpdate->name . ' RENAME TO ___old_' . $this->createTableForUpdate->name . ';
            ';

            $sql .= $this->getCreateTableSqlForUpdate();
            $sql .= '
            INSERT INTO ' . $this->createTableForUpdate->name . 
            ' SELECT * FROM ___old_' . $this->createTableForUpdate->name . ';
            ';
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
            $sql = '
            DROP TABLE ___old_' . $this->createTableForUpdate->name . ';
            COMMIT;            
            PRAGMA foreign_keys=on;            
            ';
        }

        return $sql;
    }
    
    private function getCreateTableSqlForUpdate():string
    {
        return $this->createTableForUpdate->generateSql(Table::CREATE_MODE);
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