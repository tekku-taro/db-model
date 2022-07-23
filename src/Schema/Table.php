<?php
namespace Taro\DBModel\Schema;

use Taro\DBModel\Exceptions\NotFoundException;
use Taro\DBModel\Exceptions\WrongSqlException;
use Taro\DBModel\Schema\Column\Column;
use Taro\DBModel\Schema\Column\ForeignKey;
use Taro\DBModel\Schema\Column\Index;
use Taro\DBModel\Schema\Column\PrimaryKey;
use Taro\DBModel\Utilities\DataManager\ObjectList;

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
    
    /** @var PrimaryKey drop用 */
    protected $primaryKeyToBeDropped;
    
    /** @var array<ForeignKey> */
    protected $foreignKeys = [];

    /** @var array<Index> */
    protected $indexes = [];

    /** @var Table */
    public $original;


    function __construct(string $name)
    {
        $this->name = $name;
    }

    abstract public function dropColumn(string $name);

    abstract public function dropForeign(string $name);

    abstract public function dropForeignKeyByColumn(string $column);

    abstract public function dropIndex(string $name);

    abstract public function dropIndexByColumns(...$columns);

    abstract public function addPrimaryKey(...$columns);

    abstract public function dropPrimaryKey();

    abstract public function addUnique(...$columns);

    public function checkIfExists(string $componentClass, string $name):bool
    {   
        switch ($componentClass) {
            case Column::class:
                $list = $this->columns;                
                if(isset($this->original)) {
                    $list = array_merge($list, $this->original->columns);
                }
                break;
            case ForeignKey::class:
                $list = $this->foreignKeys;                
                if(isset($this->original)) {
                    $list = array_merge($list, $this->original->foreignKeys);
                }
                break;
            case Index::class:
                $list = $this->indexes; 
                if(isset($this->original)) {
                    $list = array_merge($list, $this->original->indexes);
                }                
                break;
        }

        $nameList = [];
        foreach ($list as $object) {
            if($object->action === 'DROP') {
                $nameList = array_diff($nameList, [$object->name]);
            } else {
                $nameList[] = $object->name;
            }
        }
        
        return in_array($name, $nameList);
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
        $this->offsetDuplicates();

        if($mode === self::ALTER_MODE) {
            $this->prepareForGenerate();
        }
        $this->validate();

        switch ($mode) {
            case self::CREATE_MODE:
                $sql = $this->getCreateTableAllSql($mode);   
                break;
            case self::ALTER_MODE:
                $sql = $this->getAlterTableSql($mode);
                break;
            case self::DROP_MODE:
                $sql = 'DROP TABLE ' . $this->name . ';';
                break;
        }

        $sql = $this->addAdditonalSqls($mode, $sql);


        return $sql;
    }

    protected function getCreateTableAllSql(string $mode):string
    {
        return $this->getCreateTableSql($mode);   
    }

    private function offsetDuplicates()
    {
        $droppingColumnNames = $this->getDroppingComponentNames($this->columns);

        foreach ($this->columns as $idx => $column) {
            if(in_array($column->name, $droppingColumnNames) && $column->action !== Column::DROP_ACTION) {
                unset($this->columns[$idx]);
            }
        }
        
        $droppingFkNames = $this->getDroppingComponentNames($this->foreignKeys);

        foreach ($this->foreignKeys as $idx => $foreignKey) {
            if(in_array($foreignKey->name, $droppingFkNames) && $foreignKey->action !== ForeignKey::DROP_ACTION) {
                unset($this->foreignKeys[$idx]);
            }
        }   
        
        $droppingIdxNames = $this->getDroppingComponentNames($this->indexes);

        foreach ($this->indexes as $idx => $index) {
            if(in_array($index->name, $droppingIdxNames) && $index->action !== Index::DROP_ACTION) {
                unset($this->indexes[$idx]);
            }
        }
    }

    /**
     * @param array<mixed> $components
     * @param string $action
     * @return array<string>
     */
    private function getDroppingComponentNames(array $components, string $action = 'DROP'):array
    {
        return array_map(function($component){
            return $component->name;
        }, array_filter($components, function($component) use($action){
                return $component->action === $action;
            })
        );        
    }

    protected function addAdditonalSqls(string $mode, string $sql):string
    {
        return $sql;
    }

    protected function prepareForGenerate()
    {

    }

    protected function getCreateTableSql(string $mode):string
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

    protected function getAlterTableSql(string $mode):string
    {
        $sql = '';
        $baseSql = 'ALTER TABLE ' . $this->name . ' ';
        $pkColumns = [];

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

        if(isset($this->primaryKeyToBeDropped)) {
            $sql .= $baseSql . $this->primaryKeyToBeDropped->compile() . ';'; 
        }        

        foreach ($this->columns as $column) {
            $column->mode($mode);
            $sql .= $baseSql . $column->compile() . ';';
            if($column->isPk) {
                $pkColumns[] = $column->name;
            }            
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
                $indexSql = $index->compile();
                if($indexSql !== null) {
                    $sql .= $baseSql . $indexSql . ';';
                }
            }
        }

        if(isset($this->primaryKey) && $this->primaryKey->action === PrimaryKey::ADD_ACTION || !empty($pkColumns)) {
            $this->primaryKey->mode($mode);
            $sql .= $baseSql . $this->primaryKey->compile($pkColumns) . ';';
        }
       
        return $sql;
    }


    public function getRemainingColumnNames()
    {
        $addingColumns = [];
        $droppingColumns = [];
        foreach ($this->columns as $column) {
            if($column->action === Column::ADD_ACTION) {
                $addingColumns[] = $column->name;
            }
            if($column->action === Column::DROP_ACTION) {
                $droppingColumns[] = $column->name;
            }
        }        
        return array_diff($addingColumns, $droppingColumns);
    }    
    
    protected function getPkColumns()
    {
        $pkColumns = [];
        if(!isset($this->primaryKeyToBeDropped) && isset($this->original->primaryKey)) {
            $pkColumns = $this->original->primaryKey->columnNames;
        }
        if(isset($this->primaryKey)) {
            $pkColumns = array_merge($pkColumns, $this->primaryKey->columnNames);
        }
        
        foreach ($this->columns as $column) {
            if($column->isPk) {
                $pkColumns[] = $column->name;
            }
        }
        return array_unique($pkColumns);
    }


    protected function validate()
    {
        $pkColumns = $this->getPkColumns();
        foreach ($this->columns as $column) {
            // Columnが isPkなのにnullable設定は不可
            if(in_array($column->name, $pkColumns) && $column->nullable) {
                throw new WrongSqlException($column->name . '主キーカラムはnullable設定はできません。');
            }
            // after,before のカラム名の不存在チェック  
            if($column->after !== null) {
                if(!$this->checkIfExists(Column::class, $column->after)) {
                    throw new WrongSqlException($column->after . 'カラムが存在しません。(AFTER)');  
                }
            } 
            if($column->before !== null) {
                if(!$this->checkIfExists(Column::class, $column->before)) {
                    throw new WrongSqlException($column->before . 'カラムが存在しません。(BEFORE)');  
                }                
            } 
        }   
        
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

    public function __clone()
    {
        /** @var array<Column> */
        $columns = [];
        foreach ($this->columns as $column) {
            $columns[] = clone $column;
        }
        $this->columns = $columns;
        
        if(isset($this->primaryKey)) {
            $this->primaryKey = clone $this->primaryKey;
        }
        
        if (isset($this->primaryKeyToBeDropped)) {
            $this->primaryKeyToBeDropped = clone $this->primaryKeyToBeDropped;
        }


        /** @var array<ForeignKey> */
        $foreignKeys = [];
        foreach ($this->foreignKeys as $foreignKey) {
            $foreignKeys[] = clone $foreignKey;
        }
        $this->foreignKeys = $foreignKeys;    
        
        /** @var array<Index> */
        $indexes = [];
        foreach ($this->indexes as $index) {
            $indexes[] = clone $index;
        }
        $this->indexes = $indexes;

        if(isset($this->original)) {
            $this->original = clone $this->original;
        }         
    }
}