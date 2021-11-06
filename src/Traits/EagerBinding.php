<?php
namespace Taro\DBModel\Traits;

use Taro\DBModel\Query\Relations\BindingParams;

trait EagerBinding
{
    private $relatedModelkey;
    
    /** @var BindingParams $bindingParams */
    private $bindingParams;


    public function getRelatedModelKey()
    {
        return $this->relatedModelkey;
    }

        
    private function setBindingParams(bool $useBindParam)
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
}