<?php
namespace Taro\DBModel\Traits;

use Taro\DBModel\Exceptions\WrongSqlException;
use Taro\DBModel\Query\JoinFactory;
use Taro\DBModel\Utilities\Str;

trait CreateQuery
{
    public function select(...$selectors)
    {
        $this->query->selectors = array_merge($this->query->selectors, $selectors);
        return $this;        
    }



    public function where(...$args)
    {
        $column = $args[0];
        if(count($args) == 2) {
            $op = '=';
            $value = $this->replacePlaceholder($args[1]);
        } else {
            $op = $args[1];
            if(is_array($args[2])) {
                $array = array_map(function($val){
                    return $this->replacePlaceholder($val);
                }, $args[2]);
                $value = ' ( ' . implode(', ', $array) . ' ) ';
            } else {
                $value = $this->replacePlaceholder($args[2]);
            }
        }
        $op  = Str::modifyOperatorIfNull($op, $value);
        $clause = $column . ' ' . $op . ' ' . $value;
        $this->query->where[] = $clause;
        return $this;        
    }

    public function whereRaw(string $sql)
    {
        $this->query->where[] = $sql;

        return $this;       
    }

    public function orderBy(string $column, string $order = 'DESC')
    {
        $this->query->orderBy[] = [$column, $order];        
        return $this;
    }

    public function limit(int $limit): self
    {
        $this->query->limit = $limit;         
        return $this;
    }

    public function join(string $tableName)
    {
        $joinBuilder = JoinFactory::create(JoinFactory::JOIN, $this->query->table);
        $joinBuilder->joinTable($tableName);
        $this->query->joins[] = $joinBuilder;
        return $this;        
    }

    public function leftJoin(string $tableName)
    {
        $join = JoinFactory::create(JoinFactory::LEFT_JOIN, $tableName);
        $this->query->joins[] = $join;
        return $this;        
    }

    public function rightJoin(string $tableName)
    {
        $join = JoinFactory::create(JoinFactory::RIGHT_JOIN, $tableName);
        $this->query->joins[] = $join;
        return $this;        
    }

    public function outerJoin(string $tableName)
    {
        $join = JoinFactory::create(JoinFactory::OUTER_JOIN, $tableName);
        $this->query->joins[] = $join;
        return $this;        
    }

    public function on($leftKey, $rightKey)
    {
        $lastKey = array_key_last($this->query->joins);
        if($lastKey !== null) {
            $this->query->joins[$lastKey]->on($leftKey, $rightKey);
        } else {
            throw new WrongSqlException(' on() は join() の後に使用してください。 ');
        }     
        return $this;
    }

    public function groupBy(...$args)
    {
        $this->query->groupBy = $args;
        return $this;        
    }    


    public function whereIn($column, $value)    
    {
        if(!is_array($value)) {
            throw new WrongSqlException($value . 'は配列でなければいけません。');
        }
        return $this->where($column, 'IN', $value);
    }

    public function whereBetween($column, $min, $max)    
    {

        $min = $this->replacePlaceholder($min);
        $max = $this->replacePlaceholder($max);

        $clause = $column . ' BETWEEN ' . $min . ' AND ' . $max;
        $this->query->where[] = $clause;
        return $this;   
    }

    public function addWhere()   
    {

    }

}