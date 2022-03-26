<?php
namespace Taro\DBModel\Schema\TableLoading;

use Taro\DBModel\Schema\Table;

class TableLoader
{
    private $name;

    private $encoding;

    /** @var array<Column> */
    private $columns;

    
    /** @var array<ForeignKey> */
    private $foreignKeys;

    /** @var array<string> */
    private $pkColumns;

    /** @var array<string> */    
    private $ukColumns;

    /** @var Index */
    private $index;

    /** @var array */
    private $rawData;

    /**
     * @param Table $table
     * @param array<TableColumnInfo> $tableData
     * @return Table
     */
    public static function load(Table $table, array $tableData):Table
    {

    }

    private function readColumns()
    {

    }

    private function readName()    
    {

    }

    private function readEncoding()    
    {

    }

    private function readForiegnkeys()    
    {

    }

    private function readPks()    
    {

    }

    private function readUks()    
    {

    }

    private function readIndexes()    
    {

    }

    private function readFromRawData()
    {

    }



    private function generateTable():Table
    {

    }

}