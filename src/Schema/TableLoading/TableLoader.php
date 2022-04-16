<?php
namespace Taro\DBModel\Schema\TableLoading;

use Taro\DBModel\Schema\Column\PrimaryKey;
use Taro\DBModel\Schema\DbDriver;
use Taro\DBModel\Schema\SchemaFactory;
use Taro\DBModel\Schema\Table;
use Taro\DBModel\Utilities\DataManager\ObjectList;

class TableLoader
{
    private $name;
    
    /** @var DbDriver */
    private $driver;

    /** @var TableFetcher */
    private $fetcher;

    private $encoding;

    /** @var array<Column> */
    private $columns;

    
    /** @var array<ForeignKey> */
    private $foreignKeys;
    
    /** @var PrimaryKey */
    private $primaryKey;

    /** @var array<Index> */
    private $indexes;


    function __construct(string $name, DbDriver $driver, TableFetcher $fetcher )
    {
        $this->name = $name;
        $this->driver = $driver;
        $this->fetcher = $fetcher;
    }

    /**
     * @return Table
     */
    public function load():Table
    {
        $this->readFromRawData();

        return $this->generateTable();
    }


    private function readColumns()
    {
        foreach ($this->fetcher->tableColumns as $columnInfo) {
            $column = SchemaFactory::newColumn($this->driver, $columnInfo->name, $columnInfo->dbType, $columnInfo->tableName);
            $column->nullable($columnInfo->isNullable)->default($columnInfo->default);
            $column->autoIncrement = $columnInfo->autoIncrement;
            if($columnInfo->maxLength !== null) {
                $column->length($columnInfo->maxLength);
            }

            $this->columns[] = $column;
        }
    }

    private function readName()    
    {
        $this->name = $this->fetcher->name;
    }

    private function readEncoding()    
    {
        $this->encoding = $this->fetcher->encoding;
    }

    private function readForiegnkeys()    
    {
        foreach ($this->fetcher->tableForeignKeys as $row) {
            $foreignKey = SchemaFactory::newForeignKey($this->driver, $row->columnName, $row->tableName);
            $foreignKey->name($row->name)->references($row->referencedTable, $row->referencedColumnName);
            
            $this->foreignKeys[] = $foreignKey;            
        }
    }

    private function readPrimaryKey()    
    {
        $columnNames = [];
        foreach ($this->fetcher->tablePrimaryKey as $row) {
            $columnNames[] = $row->columnName;
        }

        $this->primaryKey = SchemaFactory::newPrimaryKey($this->driver, $columnNames);  
    }

    private function readIndexes()    
    {
        $groupedIndexes = [];
        foreach ($this->fetcher->tableIndexes as $row) {
            $groupedIndexes[$row->name][] = $row;
        }
        /** @var array<TableIndexInfo> $group */
        foreach ($groupedIndexes as $indexName => $group) {
            $groupList = new ObjectList($group);
            $index = SchemaFactory::newIndex($this->driver, $groupList->pluck('columnName'), $groupList->first()->tableName);
            $index->name($indexName)->unique($groupList->first()->isUnique);
            
            $this->indexes[] = $index;              
        }
    }

    private function readFromRawData()
    {
        $this->readName();
        $this->readEncoding();
        $this->readColumns();
        $this->readPrimaryKey();
        $this->readForiegnkeys();
        $this->readIndexes();
    }

    private function generateTable()
    {
        $table = SchemaFactory::newTable($this->name, $this->driver);
        
        $table->hydrate([
            'columns'=>$this->columns,
            'primaryKey'=>$this->primaryKey,
            'foreignKeys'=>$this->foreignKeys,
            'indexes'=>$this->indexes,
            'encoding'=>$this->encoding,
        ]);
        return $table;
    }
}