<?php
namespace Taro\DBModel\Query\Relations;

use Taro\DBModel\DB\DbManipulator;
use Taro\DBModel\Query\QueryBuilder;

class HasOne extends QueryBuilder
{
    public $fKey;

    public $modelName;

    public $fkVal;

    private $canMultiRecords = false;

    public function __construct(RelationParams $params, DbManipulator $dbManipulator, bool $useBindParam = true)
    {
        parent::__construct($dbManipulator, $params->modelName, $useBindParam);

        $this->fKey = $params->fKey;
        $this->modelName = $params->modelName;
        $this->fkVal = $params->fkVal;

        $this->where($this->fKey, $this->fkVal)
            ->where($this->fKey, 'IS NOT', null);
    }

    protected function addRelationalColumns(array $record)
    {
        $record += [$this->fKey => $this->fkVal];
        return $record;
    }
}