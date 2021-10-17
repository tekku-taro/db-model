<?php
namespace Taro\DBModel\Query\Clauses;

use Taro\DBModel\Exceptions\WrongSqlException;

class Wh
{
    /** @var WhBlockRow $data */
    private $data;

    private $useBindParam = true;

    /** @var WhParams $whParams */
    private $whParams;

    public $conjunct;


    public function __construct(bool $useBindParam = true)
    {
        $this->useBindParam = $useBindParam;
        $this->data = new WhBlockRow;
        $this->whParams = new WhParams;
    } 

    public function add(...$args)
    {
        $this->addAnd(...$args);
    }

    public function addAnd(...$args)
    {
        ['column'=>$column, 'operand'=>$operand, 'value'=>$value] = $this->reform($args);
        $block = $this->makeBlock($column, $operand, $value);
        $this->data->addAnd($block);
    }

    public function addOr(...$args)
    {
        ['column'=>$column, 'operand'=>$operand, 'value'=>$value] = $this->reform($args);
        $block = $this->makeBlock($column, $operand, $value);
        $this->data->addOr($block);
    }

    public function addBlock(WhClauseInterface $block)
    {
        $this->addAndBlock($block);
    }

    public function addAndBlock(WhClauseInterface $block)
    {
        $this->data->addAnd($block);
    }

    public function addOrBlock(WhClauseInterface $block)
    {
        $this->data->addOr($block);
    }

    public static function block(...$args)
    {
        ['column'=>$column, 'operand'=>$operand, 'value'=>$value] = self::reform($args);
        return self::makeBlock($column, $operand, $value);
    }

    public static function between($column, $min, $max): WhBlock
    {
        $block = new WhBlock();
        $block->setBetween($column, $min, $max);
        return $block;
    }


    public static function and(...$clauses)
    {
        $row  = self::makeBlockRow();
        foreach ($clauses as $clause) {
            if(!$clause instanceof WhClauseInterface) {
                throw new WrongSqlException('Wh::and() の引数は WhBlock クラスのリストである必要があります。');
            }
            $row->addAnd($clause);
        }
        return $row;
    }    

    public static function or(...$clauses)
    {        
        $row  = self::makeBlockRow();
        foreach ($clauses as $clause) {
            if(!$clause instanceof WhClauseInterface) {
                throw new WrongSqlException('Wh::or() の引数は WhBlock クラスのリストである必要があります。');
            }
            $row->addOr($clause);
        }
        return $row;
    }    


    private static function makeBlock($leftOp, $operator, $rightOp)
    {
        $block = new WhBlock();
        $block->set($leftOp, $operator, $rightOp);
        return $block;
    }

    private static function makeBlockRow()
    {
        return new WhBlockRow();
    }


    public static function reform(array $args):array
    {
        if(count($args) < 2) {
            throw new WrongSqlException('WHERE 句の引数、または絞り込む条件の配列要素は2つ以上ある必要があります。');      
        }

        $column = $args[0];
        if(count($args) == 2) {
            $operand = '=';
            $value = $args[1];
        } else {
            $operand = $args[1];
            $value = $args[2];
        }

        return ['column'=>$column, 'operand'=>$operand, 'value'=>$value];        
    }    

    public function getParams(): array
    {
        return $this->whParams->toArray();
    }


    public function compile() 
    {
        return $this->data->compile($this->whParams, $this->useBindParam);
    }

}