<?php
namespace Taro\DBModel\Traits;

use Taro\DBModel\Exceptions\WrongSqlException;
use Taro\DBModel\Utilities\Str;

trait CreateQuery
{
    public function select(...$selectors):self
    {
        $this->query->selectors = array_merge($this->query->selectors, $selectors);
        return $this;        
    }



    public function where(...$args):self
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

    public function whereRaw(string $sql):self
    {
        $this->query->where[] = $sql;

        return $this;       
    }

    public function orderBy(string $column, string $order = 'DESC'):self
    {
        $this->query->orderBy[] = [$column, $order];        
        return $this;
    }

    public function limit(int $limit): self
    {
        $this->query->limit = $limit;         
        return $this;
    }

    public function join(string $tableName, string $type = 'INNER JOIN'):self
    {
        $this->query->joins[] = ['table'=>$tableName, 'type' => $type];
        return $this;        
    }

    public function on($leftId, $op, $rightId):self
    {
        $onClause = 'ON (' . $leftId . $op . $rightId . ') ';
        end($this->query->joins)['on'] = $onClause;        
        return $this;
    }

    public function groupBy(...$args):self
    {
        $this->query->groupBy = $args;
        return $this;        
    }    


    public function whereIn($column, $value):self    
    {
        if(!is_array($value)) {
            throw new WrongSqlException($value . 'は配列でなければいけません。');
        }
        return $this->where($column, 'IN', $value);
    }

    public function whereBetween($column, $min, $max):self    
    {

        $min = $this->replacePlaceholder($min);
        $max = $this->replacePlaceholder($max);

        $clause = $column . ' BETWEEN ' . $min . ' AND ' . $max;
        $this->query->where[] = $clause;
        return $this;   
    }

    public function addWhere():self   
    {

    }

}