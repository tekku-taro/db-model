<?php
namespace Taro\DBModel\Query\Relations;

use Taro\DBModel\DB\DbManipulator;
use Taro\DBModel\Traits\EagerBinding;

class BelongsToThrough extends RelationBuilder
{
    use EagerBinding;
    
    public $fKey;  

    public $middleFKey;  

    public $middleLKey;    

    public $relKey;    

    public $middleTable;    

    public $modelName;

    public $relkVal;

    protected $canMultiRecords = false;

    public function __construct(RelationParams $params, DbManipulator $dbManipulator, bool $useBindParam = true)
    {
        parent::__construct($dbManipulator, $params->modelName, $useBindParam);

        $this->pKey = $params->pKey;
        $this->middleFKey = $params->middleFKey;
        $this->middleLKey = $params->middleLKey;
        $this->middleTable = $params->middleTable;
        $this->modelName = $params->modelName;
        $this->relkVal = $params->relkVal;
        $this->relatedModelkey = $params->relatedModelkey;

        $this->join($this->middleTable)
            ->on($this->pKey, $this->middleFKey)
            ->where($this->middleTable . '.' . $this->middleLKey, $this->relkVal)
            ->addColumn($this->middleTable . '.' . $this->middleLKey . ' AS '.RelationBuilder::MAP_KEY.' ')
            ;

            
        $this->setBindingParams($useBindParam);
    }
}