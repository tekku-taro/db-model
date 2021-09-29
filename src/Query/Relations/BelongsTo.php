<?php
namespace Taro\DBModel\Query\Relations;

use Taro\DBModel\DB\DbManipulator;
use Taro\DBModel\Query\QueryBuilder;

class BelongsTo extends QueryBuilder
{
    public $pKey;

    public $modelName;

    public $pkVal;

    private $canMultiRecords = false;

    public function __construct(RelationParams $params, DbManipulator $dbManipulator, bool $useBindParam = true)
    {
        parent::__construct($dbManipulator, $params->modelName, $useBindParam);

        $this->pKey = $params->pKey;
        $this->modelName = $params->modelName;
        $this->pkVal = $params->pkVal;

        $this->where($this->pKey, $this->pkVal);
    }
}