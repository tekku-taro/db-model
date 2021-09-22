<?php
namespace Taro\DBModel\DB;

use PDO;

class DirectSql
{
    public $table;
    
    public $sql;

    public $params;

    private $incrementedParamNo = 0;

    private $dbManipulator;

    private $sqlBlocks;

    private $compiled;

    private $useBindParam = false;

    public function __construct(DbManipulator $dbManipulator,bool $useBindParam = false)
    {
        $this->dbManipulator = $dbManipulator;
        $this->$useBindParam = $useBindParam;
    }


    public function query(string $dbName = null,bool $useBindParam = null):self
    {
        $dbManipulator = DB::database($dbName)->getManipulator();
        return new DirectSql($dbManipulator, $useBindParam);
    }

    public function prepareSql(string $sql):self
    {
        $this->sql = $sql;
        return $this;
    }

    public function bindParam($paramName, $value):self
    {
        $this->params[$paramName] = $value;
        return $this;        
    }

    public function runSql():array
    {
       return $this->dbManipulator->execute($this->sql, $this->params);
    }

    public function table($table):self
    {
        $this->table = $table;
        return $this;        
    }

    public function select(...$selectors):self
    {
        array_merge($this->sqlBlocks['selectors'], $selectors);
        return $this;        
    }
    
    private function replacePlaceholder($value): mixed
    {
        if($this->useBindParam) {
            $placeholder = $this->generatePlaceholder();
            $this->bindParam($placeholder, $value);
            return $placeholder;
        }
        return $value;

    }

    private function generatePlaceholder(): string
    {
        $this->incrementedParamNo += 1;
        return ':param'. $this->incrementedParamNo;


    }

    public function where(...$args):self
    {
        $column = $args[0];
        if(count($args) == 2) {
            $op = '=';
            $value = $args[1];
            
        } else {
            $op = $args[1];
            $value = $args[2];
        }

        $clause = $column . ' ' . $op . ' ' . $this->replacePlaceholder($value);
        $this->sqlBlocks['where'][] = $clause;
        return $this;        
    }

    public function whereRaw(string $sql):self
    {
        $this->sqlBlocks['where'][] = $sql;

        return $this;       
    }

    public function orderBy(string $column, string $order = 'DESC'):self
    {
        $this->sqlBlocks['orderBy'][] = [$column, $order];        
        return $this;
    }

    public function limit(int $limit): self
    {
        $this->sqlBlocks['limit'] = $limit;         
        return $this;
    }

    public function join(string $tableName, string $type = 'INNER JOIN'):self
    {
        $this->sqlBlocks['join'][] = ['table'=>$tableName, 'type' => $type];
        return $this;        
    }

    public function on($leftId, $op, $rightId):self
    {
        $onClause = 'ON (' . $leftId . $op . $rightId . ') ';
        end($this->sqlBlocks['join'])['on'] = $onClause;        
        return $this;
    }

    public function groupBy(...$args):self
    {
        $this->sqlBlocks['groupBy'] = $args;
        return $this;        
    }

    public function getAsArray():array
    {
        $compiled = $this->getCompiled();
        $statement = $this->dbManipulator->executeSelect($compiled, $this->params);
        $result = $statement->fetchAll();
        $statement = null;
        return $result;
    }

    public function getAsModels(string $className): array
    {
        $compiled = $this->getCompiled();
        $statement = $this->dbManipulator->executeSelect($compiled, $this->params);
        $result = $statement->fetchAll(PDO::FETCH_CLASS, $className); 
        $statement = null;
        return $result;              
    }

    private function getCompiled(): string
    {
        if($this->compiled !== null) {
            return $this->compiled;
        }
        return $this->compile();
    }

    private function compile():string
    {
        $sql = 'SELECT ' . implode(',', $this->sqlBlocks['selectors']) .' FROM ' . $this->table . ' '
        . $this->compileJoin()
        . $this->compileWhere()
        . $this->compileGroupBy()
        . $this->compileOrderBy();

        if($this->sqlBlocks['limit'] !== null) {
            $sql .= 'LIMIT ' . $this->sqlBlocks['limit'];
        }

        $this->compiled = $sql . ';';

        return $this->compiled;
    }

    private function compileJoin(): string
    {
        $joinClause = '';
        foreach ($this->sqlBlocks['join'] as $joint) {
            $joinClause .= $joint['type'] . ' ' . $joint['table'] . ' ' . $joint['on'] . ' ';
        }

        return $joinClause;
    }

    private function compileWhere(): string
    {
        return 'WHERE ' . implode(' AND ', $this->sqlBlocks['where']) . ' ';
    }

    private function compileOrderBy(): string
    {
        $orderBy = [];
        foreach ($this->sqlBlocks['orderBy'] as $columnData) {
            [$column, $order] = $columnData;
            $orderBy[] = $column . ' ' . $order;
        }

        return 'ORDER BY ' . implode(',', $orderBy) . ' ';
    }

    private function compileGroupBy(): string
    {
        return 'GROUP BY ' . implode(',', $this->sqlBlocks['groupBy']) . ' ';
    }

    public function insert(array $record):bool
    {
        $sql = 'INSERT INTO ' . $this->table . ' ';        
        $columns = array_keys($record);

        $values = array_map(function($value) {
            return $this->replacePlaceholder($value);
        }, array_values($record));


        $sql .= '(' . implode(', ', $columns) . ') VALUES (' . implode(', ', $values) . ');';
        return $this->dbManipulator->executeCUD($sql, $this->params);
    }

    public function bulkInsert(array $recordList):bool
    {
        $sql = 'INSERT INTO ' . $this->table . ' ';
        $valuesList = [];
        foreach ($recordList as $idx => $record) {
            if($idx === 0) {
                $columns = array_keys($record);
            }
            $values = array_map(function($value) {
                return $this->replacePlaceholder($value);
            }, array_values($record));

            $valuesList[] = '(' . implode(', ', $values) . ')';
        }

        $sql .= '(' . implode(', ', $columns) . ') VALUES ' . implode(', ', $valuesList) . ';';
        return $this->dbManipulator->executeCUD($sql, $this->params);
    }

    public function update(array $record):bool
    {
        $sql = 'UPDATE ' . $this->table;
        $sql .= $this->compileJoin(). ' SET ';        
        
        $setClause = [];
        foreach ($record as $column => $value) {
            $setClause[] = $column . ' = ' . $this->replacePlaceholder($value);
        }
        $sql .= implode(', ', $setClause)        
            . $this->compileWhere() . ';';

        return $this->dbManipulator->executeCUD($sql, $this->params);        
    }

    public function delete():bool
    {
        $sql = 'DELETE FROM ' . $this->table . ' ';
        $sql .= $this->compileJoin();
        $sql .= $this->compileWhere() . ';';

        return $this->dbManipulator->executeCUD($sql, $this->params);         
    }

}