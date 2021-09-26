<?php
namespace Taro\DBModel\Query\Clauses;


class WhParams
{
    private $params = [];

    public function toArray(): array
    {
        return $this->params;
    }
    
    public function add($key, $value)
    {
        $this->params[$key] = $value;
    }       
}