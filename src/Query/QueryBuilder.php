<?php
namespace Taro\DBModel\Query;

use Taro\DBModel\DB\DB;
use Taro\DBModel\DB\DbManipulator;
use Taro\DBModel\Models\Model;
use Taro\DBModel\Utilities\Paginator;

class QueryBuilder extends BaseBuilder
{
    public function __construct(DbManipulator $dbManipulator, $modelName, bool $useBindParam = true)
    {
        $this->dbManipulator = $dbManipulator;
        $this->modelName = $modelName;
        $this->useBindParam = $useBindParam;
        $this->query = new Query($dbManipulator, $modelName);
    }    

    public static function query($modelName, bool $useBindParam = true):QueryBuilder
    {
        $dbManipulator = DB::getGlobal()->getManipulator();
        $builder = new self($dbManipulator, $modelName, $useBindParam);
        return $builder;        
    }


    public function getFirst():Model    
    {
        $result = $this->executeAndFetch();

        return $this->hydrate($result, $this->modelName);
    }

    public function getAll():array
    {
        $results = $this->executeAndFetchAll();
        return $this->hydrateList($results, $this->modelName);
    }

    public function getArrayAll():array    
    {
        return $this->executeAndFetchAll();
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

    public function getPaginator(int $number):Paginator    
    {

    }

    public function findById($id):Model    
    {
        $this->query->where = [];
        
        $this->where('id', $id);
        $result = $this->executeAndFetch();

        return $this->hydrate($result);
    }

    public function with():self    
    {

    }

    protected function checkInput() 
    {

    }


    public function isRelatedModel():bool
    {

    }
}