<?php
namespace Taro\DBModel\Query\Relations;

use Taro\DBModel\Query\QueryBuilder;

class HasMany extends QueryBuilder
{
    public $relKey;

    public $fKey;

    public $modelName;

    public $keyVal;


    public function __construct()
    {
        
    }
}