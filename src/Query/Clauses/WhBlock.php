<?php
namespace Taro\DBModel\Query\Clauses;

use Taro\DBModel\Traits\ParamsTrait;
use Taro\DBModel\Utilities\Str;

class WhBlock implements WhClauseInterface
{
    use ParamsTrait;

    public $leftOp;

    public $rightOp;

    public $operator;

    public $isBetween = false;

    public $max;

    public $min;

    private $conjunct;

    /** @var Whereparams $params */    
    private $params;
    

    public function set($leftOp, $operator, $rightOp)
    {
        $this->leftOp =$leftOp;
        $this->operator =trim($operator);
        $this->rightOp =$rightOp;
    }

    public function setBetween($column, $min, $max)
    {
        $this->leftOp = $column;
        $this->min = $min;
        $this->operator = 'BETWEEN';
        $this->max = $max;
        $this->isBetween = true;
    }

    public function compile(WhParams $params, bool $useBindParam) 
    {      
        $this->params = $params;
        $this->useBindParam = $useBindParam;

        if($this->isBetween) {
            $min = $this->replacePlaceholder($this->min);
            $max = $this->replacePlaceholder($this->max);            
            $sql = $this->leftOp . ' BETWEEN ' . $min . ' AND ' . $max;
        } else {
            if(is_array($this->rightOp)) {
                $array = array_map(function($val){
                    return $this->replacePlaceholder($val);
                }, $this->rightOp);
                $rightOp = ' ( ' . implode(', ', $array) . ' ) ';
            } else {
                $rightOp = $this->replacePlaceholder($this->rightOp);
            }
            $sql = $this->leftOp . ' ' . Str::modifyOperatorIfNull($this->operator, $rightOp) . ' ' . $rightOp;
        }
        
        return $sql;
    }


    public function bindParam($paramName, $value):void
    {
        $this->params->add($paramName, $value);     
    }

    public function getConjunct(): string
    {
        return $this->conjunct;
    }
    
    public function setConjunct(string $value)
    {
        $this->conjunct = $value;
    }
}