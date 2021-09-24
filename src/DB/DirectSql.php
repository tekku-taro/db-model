<?php
namespace Taro\DBModel\DB;

use PDO;
use Taro\DBModel\Exceptions\WrongSqlException;

class DirectSql
{
    public $table;
    
    public $sql;

    public $params = [];

    private $incrementedParamNo = 0;

    private $dbManipulator;

    private $sqlBlocks = [
        'selectors'=>[]
    ];

    private $compiled;

    private $useBindParam;

    public function __construct(DbManipulator $dbManipulator, bool $useBindParam = true)
    {
        $this->dbManipulator = $dbManipulator;
        $this->useBindParam = $useBindParam;
    }


    public static function query(bool $useBindParam = true):self
    {
        $dbManipulator = DB::getGlobal()->getManipulator();
        $query = new self($dbManipulator, $useBindParam);
        return $query;        
    }

    public static function queryToDb(?string $connName, bool $useBindParam = true):self
    {
        $dbManipulator = DB::database($connName)->getManipulator();
        $query = new self($dbManipulator, $useBindParam);
        return $query;
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
        $this->sqlBlocks['selectors'] = array_merge($this->sqlBlocks['selectors'], $selectors);
        return $this;        
    }
    
    private function replacePlaceholder($value)
    {
        if($this->useBindParam) {
            if($value[0] === ':') {
                return $value;
            }
            $placeholder = $this->generatePlaceholder();
            $this->bindParam($placeholder, $value);
            return $placeholder;
        }
        return '"' . $value . '"';

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
            $value = $this->replacePlaceholder($args[1]);
        } else {
            $op = $args[1];
            if(is_array($args[2])) {
                $array = array_map(function($val){
                    return $this->replacePlaceholder($val);
                }, $args[2]);
                $value = ' ( ' . implode(', ', $array) . ' ) ';
            } else {
                $value = $this->replacePlaceholder($args[2]);
            }
        }

        $clause = $column . ' ' . $op . ' ' . $value;
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
        $sql = 'SELECT ' . $this->compileSelectors() .' FROM ' . $this->table . ' '
        . $this->compileJoin()
        . $this->compileWhere()
        . $this->compileGroupBy()
        . $this->compileOrderBy()
        . $this->compileLimit();

        $this->compiled = $sql . ';';

        return $this->compiled;
    }

    private function compileSelectors(): string
    {
        if(empty($this->sqlBlocks['selectors'])) {
            $selectClause = '*';
        } else {
            $selectClause = implode(',', $this->sqlBlocks['selectors']);
        }

        return $selectClause;
    }

    private function compileJoin(): string
    {
        if(!isset($this->sqlBlocks['join'])) {
            return '';
        }

        $joinClause = '';
        foreach ($this->sqlBlocks['join'] as $joint) {
            $joinClause .= $joint['type'] . ' ' . $joint['table'] . ' ' . $joint['on'] . ' ';
        }

        return $joinClause;
    }

    private function compileWhere(): string
    {
        if(!isset($this->sqlBlocks['where'])) {
            return '';
        }

        return 'WHERE ' . implode(' AND ', $this->sqlBlocks['where']) . ' ';
    }

    private function compileOrderBy(): string
    {
        if(!isset($this->sqlBlocks['orderBy'])) {
            return '';
        }

        $orderBy = [];
        foreach ($this->sqlBlocks['orderBy'] as $columnData) {
            [$column, $order] = $columnData;
            $orderBy[] = $column . ' ' . $order;
        }

        return 'ORDER BY ' . implode(',', $orderBy) . ' ';
    }

    private function compileGroupBy(): string
    {
        if(!isset($this->sqlBlocks['groupBy'])) {
            return '';
        }

        return 'GROUP BY ' . implode(',', $this->sqlBlocks['groupBy']) . ' ';
    }

    private function compileLimit(): string
    {
        if(!isset($this->sqlBlocks['limit'])) {
            return '';
        }

        return 'LIMIT ' . $this->sqlBlocks['limit'];
    }

    public function insert(array $record):bool
    {
        $this->validateSqlBlocks(['record', 'table'], $record);

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
        $this->validateSqlBlocks(['record', 'table'], $recordList);

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
        $this->validateSqlBlocks(['record', 'table', 'where'], $record);

        $sql = 'UPDATE ' . $this->table;
        $sql .= $this->compileJoin(). ' SET ';        
        
        $setClause = [];
        foreach ($record as $column => $value) {
            $setClause[] = $column . ' = ' . $this->replacePlaceholder($value);
        }
        $sql .= implode(', ', $setClause) . ' '        
            . $this->compileWhere() . ';';

        return $this->dbManipulator->executeCUD($sql, $this->params);        
    }

    public function delete():bool
    {
        $this->validateSqlBlocks(['table', 'where']);

        $sql = 'DELETE FROM ' . $this->table . ' ';
        $sql .= $this->compileJoin();
        $sql .= $this->compileWhere() . ';';

        return $this->dbManipulator->executeCUD($sql, $this->params);         
    }

    private function validateSqlBlocks(array $checkItems, $data = null): bool
    {
        if(in_array('table', $checkItems) && $this->table === null) {            
            throw new WrongSqlException(' テーブル名が空欄です。 ');
        }
        if(in_array('record', $checkItems) && $data === null) {            
            throw new WrongSqlException(' 保存するデータがありません。 ');
        }
        if(in_array('where', $checkItems) && !isset($this->sqlBlocks['where'])) {            
            throw new WrongSqlException(' WHERE で対象レコードを制限していません。 ');
        }

        return true;
    }

}