<?php
namespace Taro\DBModel\Query;

use Taro\DBModel\DB\DbManipulator;
use Taro\DBModel\Models\Model;
use Taro\DBModel\Traits\CreateQuery;
use Taro\DBModel\Traits\ParamsTrait;
use Taro\DBModel\Utilities\DataManager\ArrayList;
use Taro\DBModel\Utilities\DataManager\ObjectList;

class BaseBuilder
{
    use ParamsTrait;
    use CreateQuery;

    /** @var Query $query */
    public $query;

    public $modelName;

    private $dbManipulator;


    public function __construct(DbManipulator $dbManipulator, $modelName, bool $useBindParam = true)
    {
        $this->dbManipulator = $dbManipulator;
        $this->modelName = $modelName;
        $this->useBindParam = $useBindParam;
        $this->query = new Query($dbManipulator, $modelName);
    } 

    protected function executeAndFetchAll()
    {
        $statement = $this->query->execute();
        $results = $statement->fetchAll();
        $statement = null;
        if($results === false) {
            return null;
        }
        return $results;
    }

    protected function executeAndFetch()
    {
        $statement = $this->query->execute();
        $results = $statement->fetch();
        $statement = null;
        if($results === false) {
            return null;
        }
        return $results;
    }


    // 集計関数

    public function count($column = null, $alias = null)
    {
        return $this->aggregateMethod('COUNT', $column, $alias);
    }

    public function average($column, $alias = null)
    {
        return $this->aggregateMethod('AVG', $column, $alias);
    }

    public function max($column, $alias = null)
    {
        return $this->aggregateMethod('MAX', $column, $alias);
    }

    public function min($column, $alias = null)
    {
        return $this->aggregateMethod('MIN', $column, $alias);
    }

    public function sum($column, $alias = null)
    {
        return $this->aggregateMethod('SUM', $column, $alias);
    }

    /**
     * @param string $method
     * @param string $column
     * @param string $alias
     * @return null|int|ArrayList  groupByでグループ化した場合は、 ArrayList が返される
     */
    protected function aggregateMethod($method, $column = null, $alias = null)
    {
        if($column === null) {
            $column = '*';             
        }
        if($alias === null) {
            $alias = $this->createAggregateColumnName($method, $column);
        }

        $this->query->setAggregateSelector($method, $column, $alias);
        $results = $this->executeAndFetchAll();

        if($results === null) {
            return null;
        }

        if(count($results) === 1 && count($results[0]) === 1) {
            return $results[0][$alias];
        }

        return $this->arrayList($results);
    }

    protected function createAggregateColumnName($method, $column)
    {
        if($column === '*') {
            return strtolower($method);
        }

        return $column . '_' . strtolower($method);
    }

    protected function checkInput() 
    {

    }

    public function toSql(): string
    {
        return $this->query->getCompiled();
    }

    public function getParams(): array
    {
        return $this->query->params;
    }

    public function insert(array $record):bool
    { 
        $this->query->params = [];
        $record = $this->addRelationalColumns($record);
        $record = $this->placeholdersForRecord($record);

        return $this->query->executeInsert($record);
    }

    public function bulkInsert(array $recordList):bool
    {
        $this->query->params = [];
        $modifiedList = array_map(function($record) {
            $record = $this->addRelationalColumns($record);            
            return $this->placeholdersForRecord($record);
        }, $recordList);        

        return $this->query->executeBulkInsert($modifiedList);
    }

    public function update(array $record):bool
    {
        $record = $this->placeholdersForRecord($record);

        return $this->query->executeUpdate($record);  
    }

    public function delete():bool
    {
        return $this->query->executeDelete();         
    }


    protected function addRelationalColumns(array $record)
    {
        return $record;
    }

    /**
     * @param array[] $records
     * @return ArrayList
     */
    protected function arrayList(?array $records = []):ArrayList
    {
        $arrayList = new ArrayList($records);

        return $arrayList;
    }

    /**
     * @param array[] $records
     * @return ObjectList
     */
    protected function hydrateList(array $records, $className):ObjectList
    {
        $modelList = new ObjectList();
        foreach ($records as $record) {
            $modelList->push($this->hydrate($record, $className));
        }
        return $modelList;
    }

    protected function hydrate(array $record, $className):Model    
    {
        // $modelName = Task::class;
        /** @var Model $model */
        $model = new $className;
        $model->initWith($record);
        return $model;
    }

}