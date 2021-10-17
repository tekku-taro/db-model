<?php
namespace Taro\DBModel\Query\Relations;

use Taro\DBModel\DB\DbManipulator;
use Taro\DBModel\Query\QueryBuilder;

class HasManyThrough extends QueryBuilder
{
    public $fKey;  

    public $middleFKey;  

    public $middleLKey;    

    public $relKey;    

    public $middleTable;    

    public $modelName;

    public $relkVal;

    private $canMultiRecords = true;

    public function __construct(RelationParams $params, DbManipulator $dbManipulator, bool $useBindParam = true)
    {
        parent::__construct($dbManipulator, $params->modelName, $useBindParam);

        $this->fKey = $params->fKey;
        $this->middleFKey = $params->middleFKey;
        $this->middleLKey = $params->middleLKey;
        $this->middleTable = $params->middleTable;
        $this->modelName = $params->modelName;
        $this->relkVal = $params->relkVal;

        $this->join($this->middleTable)
            ->on($this->fKey, $this->middleLKey)
            ->where($this->middleTable . '.' . $this->middleFKey, $this->relkVal)
            ;
    }
   
}