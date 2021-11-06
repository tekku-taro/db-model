<?php
namespace Taro\DBModel\Query;

use PDOStatement;
use Taro\DBModel\DB\DbManipulator;
use Taro\DBModel\Exceptions\WrongSqlException;
use Taro\DBModel\Query\Joins\Join;
use Taro\DBModel\Traits\SqlBaseTrait;
use Taro\DBModel\Utilities\Inflect;
use Taro\DBModel\Utilities\Str;

class Query
{
    use SqlBaseTrait;

    /** @var DbManipulator $dbManipulator */
    private $dbManipulator;

    public $selectors = [];

    public $otherColumns = [];

    public $where;

    public $orderBy;

    public $limit;

    public $offset;

    public $groupBy;

    /** @var RelationList $relations */
    public $relations;

    public $params = [];

    public $modelName;

    public $table;

    private $compiled;

    public $joins;

    public function __construct(DbManipulator $dbManipulator, $modelName = null)
    {
        $this->dbManipulator = $dbManipulator;
        $this->modelName = $modelName;
        $this->relations = new RelationList;
        if($modelName !== null) {
            $this->table = Inflect::pluralize(Str::snakeCase(Str::getShortClassName($this->modelName)));
        }
    }    

    public function execute():PDOStatement
    {
        $compiled = $this->compile();
        return $this->dbManipulator->executeAndStatement($compiled, $this->params);
    }

    public function executeWithoutCompile():PDOStatement
    {
        return $this->dbManipulator->executeAndStatement($this->compiled, $this->params);
    }

    public function getParams():array
    {
        return $this->params;
    }
    
    public function raw():self
    {

    }

    public function setCompiled($sql):void
    {
        $this->compiled = $sql;
    }

    public function getCompiled(): string
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
        if(empty($this->selectors)) {
            $selectClause = $this->table . '.*';
        } else {
            $selectClause = implode(',', $this->selectors);
        }

        if(!empty($this->otherColumns)) {
            $selectClause .= ',' . implode(',', $this->otherColumns);
        }

        return $selectClause;
    }

    private function compileJoin(): string
    {
        if(!isset($this->joins)) {
            return '';
        }

        $joinClause = '';
        /** @var Join $join */
        foreach ($this->joins as $join) {
            $joinClause .= $join->toSql();
        }

        return $joinClause;
    }

    private function compileWhere(): string
    {
        if(!isset($this->where)) {
            return '';
        }

        return 'WHERE ' . implode(' AND ', $this->where) . ' ';
    }

    private function compileOrderBy(): string
    {
        if(!isset($this->orderBy)) {
            return '';
        }

        $orderBy = [];
        foreach ($this->orderBy as $columnData) {
            [$column, $order] = $columnData;
            $orderBy[] = $column . ' ' . $order;
        }

        return 'ORDER BY ' . implode(',', $orderBy) . ' ';
    }

    private function compileGroupBy(): string
    {
        if(!isset($this->groupBy)) {
            return '';
        }

        return 'GROUP BY ' . implode(',', $this->groupBy) . ' ';
    }

    private function compileLimit(): string
    {
        if(!isset($this->limit)) {
            return '';
        }

        return 'LIMIT ' . $this->limit;
    }


    public function executeInsert(array $record):bool
    {
        $sql = $this->prepareInsert($record);
        return $this->dbManipulator->executeAndBoolResult($sql, $this->params);
    }

    public function executeBulkInsert(array $recordList):bool
    {        
        $sql = $this->prepareBulkInsert($recordList);
        return $this->dbManipulator->executeAndBoolResult($sql, $this->params);
    }

    public function executeUpdate(array $record):bool
    {
        $sql = $this->prepareUpdate($record);
        $sql .= $this->compileWhere() . ';';

        return $this->dbManipulator->executeAndBoolResult($sql, $this->params);        
    }

    public function executeDelete():bool
    {
        $sql = $this->prepareDelete();
        $sql .= $this->compileJoin();
        $sql .= $this->compileWhere() . ';';

        return $this->dbManipulator->executeAndBoolResult($sql, $this->params);         
    }

    private function validateSqlBlocks(array $checkItems, $data = null): bool
    {
        if(in_array('table', $checkItems) && $this->table === null) {            
            throw new WrongSqlException(' テーブル名がありません。 ');
        }
        if(in_array('record', $checkItems) && $data === null) {            
            throw new WrongSqlException(' 保存するデータがありません。 ');
        }
        if(in_array('where', $checkItems) && !isset($this->where)) {            
            throw new WrongSqlException(' WHERE で対象レコードを制限していません。 ');
        }

        return true;
    }

    public function setSelector($selector)
    {
        if(strpos($selector, '.') === false) {
            $selector = $this->table . '.' . $selector;
        }
        $this->selectors[] = $selector;
    }
}
