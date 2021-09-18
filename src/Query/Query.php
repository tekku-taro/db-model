<?php
namespace Taro\DBModel\Query;

class Query
{
    public $selector;

    public $where;

    public $orderBy;

    public $limit;

    public $offset;

    public $groupBy;

    public $relations;

    public $binds;

    public $modelName;

    public $joins;

    public function compile():string
    {

    }
    public function getBinds():array
    {

    }
    public function raw():self
    {

    }
}