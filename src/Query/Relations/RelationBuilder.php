<?php
namespace Taro\DBModel\Query\Relations;

use Taro\DBModel\Query\QueryBuilder;

class RelationBuilder extends QueryBuilder
{
    public const MAP_KEY = 'mapkey';

    protected $relatedModelkey;
    
    /** @var BindingParams $bindingParams */
    protected $bindingParams;

    protected $canMultiRecords;

    public function getRelatedModelKey()
    {
        return $this->relatedModelkey;
    }

        
    protected function setBindingParams(bool $useBindParam)
    {
        $this->bindingParams = new BindingParams($this->query, $useBindParam);
    }


    // 保存したidx のwhere 句の id を IDリストで上書きする
    public function updateWhereWithIdList(array $idList) 
    {
        $whereIdx = $this->bindingParams->whereIdx;
        unset($this->query->where[$whereIdx]);
        $this->where($this->bindingParams->whereColumn,'IN', $idList);
        if($this->bindingParams->useBindParam) {
            unset($this->query->params[$this->bindingParams->paramName]);
        } 
    } 
    
    
    public function getAsMap()
    {
        $objectList = $this->getAll();

        $mapKey = $this->getMapKey();

        if($this->canMultiRecords) {
            return $objectList->groupBy($mapKey);

        } else {
            return $objectList->getObjectMap($mapKey);
        }
    }

    protected function getMapKey()
    {
        return self::MAP_KEY;
    }
}