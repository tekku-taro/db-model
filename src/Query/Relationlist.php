<?php
namespace Taro\DBModel\Query;

use ArrayIterator;
use Countable;
use IteratorAggregate;

class RelationList implements IteratorAggregate, Countable
{
    protected $list = [];
    
    public function setList(array $list)
    {
        $this->list = $list;
    }

    public function deleteAt($idx)
    {
        unset($this->list[$idx]);
        $this->list = array_values($this->list);
    }

    public function toArray()
    {
        return $this->list;
    }

    public function getIterator():ArrayIterator
    {
      return new ArrayIterator($this->list);
    }

    public function count():int
    {
      return count($this->list); 
    }    
}