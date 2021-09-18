<?php
namespace Taro\DBModel\DB;


class DirectSql
{
    public $table;
    
    public $sql;

    public $params;

    private $dbManipulator;

    private $sqlBlocks;

    public function query():self
    {
        
    }

    public function prepareSql($sql):self
    {
        
    }

    public function bindParam($paramName, $value):self
    {
        
    }

    public function runSql():array
    {
        
    }

    public function table($table):self
    {
        
    }

    public function select($selectors):self
    {
        
    }

    public function where($column, $value):self
    {
        
    }

    public function whereRaw($sql):self
    {
        
    }

    public function orderBy():self
    {

    }

    public function limit(): self
    {
        return $this;
    }

    public function join($tableName):self
    {
        
    }

    public function on($leftId, $op, $rightId):self
    {
        
    }

    public function groupBy():self
    {
        
    }

    public function getAsArray():array
    {
        
    }

    public function getAsModels($modelName)
    {
        
    }

    private function compile():string
    {
        
    }

    public function assocInsert():bool
    {
        
    }

    public function assocUpdate():bool
    {
        
    }

    public function delete():bool
    {
        
    }

}