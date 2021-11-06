<?php
namespace Taro\DBModel\Query\Relations;

use Taro\DBModel\Query\Query;

class BindingParams
{
    public $whereIdx;
    
    public $paramName;
    
    public $whereColumn;
    
    public $whereValue;

    public $useBindParam;

    function __construct(Query $query, bool $useBindParam)
    {
        $this->whereIdx = array_key_last($query->where);
        [$this->whereColumn, $op, $this->whereValue] = explode(' ', $query->where[$this->whereIdx]) ;

        if($useBindParam) {
            $this->paramName = $this->whereValue;
            $this->useBindParam = $useBindParam;
        }        
    }   
}