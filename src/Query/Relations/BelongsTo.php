<?php
namespace Taro\DBModel\Query\Relations;

use Taro\DBModel\DB\DbManipulator;
use Taro\DBModel\Traits\EagerBinding;

class BelongsTo extends RelationBuilder
{
    public $pKey;

    public $modelName;

    public $pkVal;

    protected $canMultiRecords = false;

    public function __construct(RelationParams $params, DbManipulator $dbManipulator, bool $useBindParam = true)
    {
        parent::__construct($dbManipulator, $params->modelName, $useBindParam);

        $this->pKey = $params->pKey;
        $this->modelName = $params->modelName;
        $this->pkVal = $params->pkVal;
        $this->relatedModelkey = $params->relatedModelkey;

        $this->where($this->pKey, $this->pkVal);

        $this->setBindingParams($useBindParam);
    }

    protected function getMapKey()
    {
        return $this->pKey;
    }

}