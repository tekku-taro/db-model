<?php
namespace Taro\DBModel\Schema;

use Taro\DBModel\Exceptions\NotFoundException;
use Taro\DBModel\Schema\Column\Column;
use Taro\DBModel\Schema\Column\ForeignKey;
use Taro\DBModel\Schema\Column\Index;
use Taro\DBModel\Schema\Column\PrimaryKey;

abstract class Table
{
    public const CREATE_MODE = 'CREATE';
    public const ALTER_MODE = 'ALTER';
    public const DROP_MODE = 'DROP'; 

    public $name;

    protected $encoding;

    /** @var array<Column> */
    protected $columns = [];
    
    /** @var PrimaryKey */
    protected $primaryKey;
    
    /** @var array<ForeignKey> */
    protected $foreignKeys = [];

    /** 
     * @var array<string,array<string>> 
     * ['add'=>[], 'remove'=>[]]
     * 
     * */
    protected $pkColumns;

    /** 
     * @var array<string,array<string>> 
     * ['add'=>[], 'remove'=>[]]
     * 
     * */    
    protected $ukColumns;

    /** @var array<Index> */
    protected $indexes = [];

    /** @var Table */
    public $original;


    function __construct(string $name)
    {
        $this->name = $name;
    }

    abstract public function addColumn(string $name, string $columnType):Column;

    abstract public function changeColumn(string $name,string $newName = null):Column;

    abstract public function dropColumn(string $name);

    abstract public function addForeign(string $column):ForeignKey;

    abstract public function addIndex(...$columns):Index;

    abstract public function dropForeign(string $name);

    abstract public function dropForeignKeyByColumn(string $column);

    abstract public function dropIndex(string $name);

    abstract public function dropIndexByColumns(...$columns);

    abstract public function addPrimaryKey(...$columns);

    abstract public function addUnique(...$columns);

    public function checkIfExists(string $componentClass, string $name):bool
    {
        switch ($componentClass) {
            case Column::class:
                $list = $this->columns;                
                break;
            case ForeignKey::class:
                $list = $this->foreignKeys;                
                break;
            case Index::class:
                $list = $this->indexes; 
                break;
        }

        foreach ($list as $object) {
            if($object->name === $name) {
                return true;
            }
        }
        return false;
    }


    protected function getColumn(string $name):Column
    {
        foreach ($this->columns as $column) {
            if($column->name === $name) {
                return $column;
            }
        }            

        throw new NotFoundException($name . 'というカラムは存在しません。');
    }

    protected function getForeign(string $name):ForeignKey
    {
        foreach ($this->foreignKeys as $foreignKey) {
            if($foreignKey->name === $name) {
                return $foreignKey;
            }
        }        
        throw new NotFoundException($name . 'という外部キーは存在しません。');
    }

    protected function getPrimaryKey():PrimaryKey
    {
        if(isset($this->primaryKey)) {
            return $this->primaryKey;
        }        
        throw new NotFoundException($this->name . 'テーブルに主キーは存在しません。');
    }

    /**
     * @param array<string> $columns
     * @return ForeignKey
     */
    protected function getForeignByColumn($column):ForeignKey
    {
        foreach ($this->foreignKeys as $foreignKey) {
            if($column === $foreignKey->columnName) {
                return $foreignKey;
            }
        }         
        throw new NotFoundException($column . 'カラムからなる外部キーは存在しません。');
    }

    protected function getIndex(string $name):Index
    {
        foreach ($this->indexes as $index) {
            if($index->name === $name) {
                return $index;
            }
        }        
        throw new NotFoundException($name . 'というインデックスは存在しません。');
    }

    /**
     * @param array<string> $columns
     * @return Index
     */
    protected function getIndexByColumns(array $columns):Index
    {
        foreach ($this->indexes as $index) {
            if(twoArraysHaveSameElements($columns, $index->columnNames)) {
                return $index;
            }
        }         
        throw new NotFoundException(implode(',', $columns) . 'カラムからなるインデックスは存在しません。');
    }

    public function generateSql(string $mode):string
    {
        switch ($mode) {
            case self::CREATE_MODE:
                $sql = $this->getCreateTableSql($mode);   
                break;
            case self::ALTER_MODE:
                $sql = $this->getAlterTableSql($mode);
                break;
            case self::DROP_MODE:
                $sql = 'DROP TABLE ' . $this->name . ';';
                break;
        }

        return $sql;
    }

    private function getCreateTableSql(string $mode):string
    {
        $sql = 'CREATE TABLE ' . $this->name . ' ( ';
        $pkColumns = [];
        $columnSql = [];
        $foreignSql = [];
        $indexSql = [];
        foreach ($this->columns as $column) {
            $column->mode($mode);
            $columnSql[] = $column->compile();
            if($column->isPk) {
                $pkColumns[] = $column->name;
            }
        }
        if(!empty($columnSql)) {
            $sql .= implode(',', $columnSql);
        }

        foreach ($this->foreignKeys as $foreignKey) {
            $foreignKey->mode($mode);
            $foreignSql[] = $foreignKey->compile();
        }
        if(!empty($foreignSql)) {
            $sql .= ',' . implode(',', $foreignSql);
        }

        foreach ($this->indexes as $index) {
            $index->mode($mode);
            $indexSql[] = $index->compile();
        }
        if(!empty($indexSql)) {
            $sql .= ',' . implode(',', $indexSql);
        }

        if(isset($this->primaryKey) || !empty($pkColumns)) {
            $sql .= ',' .  $this->compilePk($pkColumns); 
        }

        $sql .= ' );';    
        
        return $sql;
    }

    /**
     * @param array<string> $pkColumns
     * @return string
     */
    abstract protected function compilePk(array $pkColumns):string;

    private function getAlterTableSql(string $mode):string
    {
        $sql = '';
        $baseSql = 'ALTER TABLE ' . $this->name . ' ';

        foreach ($this->foreignKeys as $foreignKey) {
            if($foreignKey->action === ForeignKey::DROP_ACTION) {
                $foreignKey->mode($mode);
                $sql .= $baseSql . $foreignKey->compile() . ';';
            }
        }

        foreach ($this->indexes as $index) {
            if ($index->action === Index::DROP_ACTION) {
                $index->mode($mode);
                $sql .= $baseSql . $index->compile() . ';';
            }
        }        

        if(isset($this->primaryKey) && $this->primaryKey->action === PrimaryKey::DROP_ACTION) {
            $sql .= $this->primaryKey->compile() . ';'; 
        }        

        foreach ($this->columns as $column) {
            $column->mode($mode);
            $sql .= $baseSql . $column->compile() . ';';
        }

        foreach ($this->foreignKeys as $foreignKey) {
            if ($foreignKey->action === ForeignKey::ADD_ACTION) {
                $foreignKey->mode($mode);
                $sql .= $baseSql . $foreignKey->compile() . ';';
            }
        }

        foreach ($this->indexes as $index) {
            if ($index->action === Index::ADD_ACTION) {
                $index->mode($mode);
                $sql .= $baseSql . $index->compile() . ';';
            }
        }

        if(isset($this->primaryKey) && $this->primaryKey->action === PrimaryKey::ADD_ACTION) {
            $sql .= $this->primaryKey->compile() . ';';
        }
       
        return $sql;
    }

    private function validate()
    {
        // Columnが isPk,isUkなのにnullable設定は不可
        // after,before のカラム名の不存在チェック       
    }

    public function hydrate(array $data)
    {
        if(!empty($data['columns'])) {
            $this->columns = $data['columns'];
        }
        if(!empty($data['primaryKey'])) {
            $this->primaryKey = $data['primaryKey'];
        }
        if(!empty($data['foreignKeys'])) {
            $this->foreignKeys = $data['foreignKeys'];
        }
        if(!empty($data['indexes'])) {
            $this->indexes = $data['indexes'];
        }
        if(!empty($data['encoding'])) {
            $this->encoding = $data['encoding'];
        }
    }
}