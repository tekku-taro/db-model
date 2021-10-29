<?php
namespace Taro\DBModel\Query;

use Taro\DBModel\DB\DB;
use Taro\DBModel\DB\DbManipulator;
use Taro\DBModel\Utilities\DataManager\ArrayList;
use Taro\DBModel\Utilities\DataManager\ObjectList;

class DirectSql extends BaseBuilder
{
    public function __construct(DbManipulator $dbManipulator, bool $useBindParam = true)
    {
        parent::__construct($dbManipulator, null, $useBindParam);
    }

    public static function query(bool $useBindParam = true):self
    {
        $dbManipulator = DB::getGlobal()->getManipulator();
        $builder = new self($dbManipulator, $useBindParam);
        return $builder;        
    }

    public static function queryToDb(?string $connName, bool $useBindParam = true):self
    {
        $dbManipulator = DB::database($connName)->getManipulator();
        $query = new self($dbManipulator, $useBindParam);
        return $query;
    }

    public function prepareSql(string $sql):self
    {
        $this->query->setCompiled($sql);
        return $this;
    }


    public function runSql()
    {
        $statement = $this->query->executeWithoutCompile();

        if($this->isSelectQuery()) {
            $result = $statement->fetchAll();
        } else {
            $result = true;
        }

        $statement = null;
        if($result === false) {
            return null;
        }
        return $result;
    }

    protected function isSelectQuery()
    {
        if(preg_match('/^select /i', trim($this->query->getCompiled()))) {
            return true;
        }
        return false;
    }

    public function table($table):self
    {
        $this->query->table = $table;
        return $this;        
    }


    public function getAsArray():ArrayList
    {
        $records = $this->executeAndFetchAll();

        return $this->arrayList($records);        
    }

    public function getAsModels(string $className): ObjectList
    {
        $result = $this->executeAndFetchAll();
        $modelList = $this->hydrateList($result, $className); 
        return $modelList;              
    }

}