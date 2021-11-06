<?php
namespace Taro\DBModel\Query\Relations;

use Taro\DBModel\DB\DbManipulator;
use Taro\DBModel\Query\QueryBuilder;
use Taro\DBModel\Traits\EagerBinding;

class HasMany extends RelationBuilder
{
    use EagerBinding;
    
    public $fKey;

    public $modelName;

    public $fkVal;

    protected $canMultiRecords = true;

    public function __construct(RelationParams $params, DbManipulator $dbManipulator, bool $useBindParam = true)
    {
        parent::__construct($dbManipulator, $params->modelName, $useBindParam);

        $this->fKey = $params->fKey;
        $this->modelName = $params->modelName;
        $this->fkVal = $params->fkVal;
        $this->relatedModelkey = $params->relatedModelkey;

        $this->where($this->fKey, 'IS NOT', null)
            ->where($this->fKey, $this->fkVal)
            ;

            
        $this->setBindingParams($useBindParam);
    }

    protected function addRelationalColumns(array $record)
    {
        $record += [$this->fKey => $this->fkVal];
        return $record;
    }

    protected function getMapKey()
    {
        return $this->fKey;
    }    
}