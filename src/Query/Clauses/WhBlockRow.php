<?php
namespace Taro\DBModel\Query\Clauses;

use Taro\DBModel\Exceptions\WrongSqlException;

class WhBlockRow implements WhClauseInterface
{
    public $conjunct;

    private const AND_OPERATOR = 'AND';

    private const OR_OPERATOR = 'OR';
    
    /** @var array<WhClauseInterface> $rowBlocks */
    public $rowBlocks = [];

    private static $level = 0;
    
    private const MAX_LEVEL = 6;


    public function addAnd(WhClauseInterface $block)
    {
        $block->setConjunct(self::AND_OPERATOR);
        $this->rowBlocks[] = $block;
    }

    public function addOr(WhClauseInterface $block)
    {
        $block->setConjunct(self::OR_OPERATOR);
        $this->rowBlocks[] = $block;
    }

    public function compile(WhParams $params, bool $useBindParam) 
    {
        $this->incrementAndCheckLevel();

        $sql = '( ';
        foreach ($this->rowBlocks as $idx => $block) {
            $blockSql = $this->compileBlock($block, $params, $useBindParam);
            if($idx === 0) {
                $sql .= $blockSql;
            } else {
                $sql = $this->connectBlocks( $sql, $block->getConjunct(), $blockSql);
            }
        }
        $sql .= ' )';
        $this->decrementLevel();
        return $sql;
    }

    private function connectBlocks($sqlA, $conjunct, $sqlB)
    {
        return $sqlA . ' ' . $conjunct . ' ' . $sqlB;        
    }

    private function compileBlock(WhClauseInterface $block, WhParams $params, bool $useBindParam)
    {
        return $block->compile($params, $useBindParam);
    }


    private function incrementAndCheckLevel()
    {
        self::$level += 1;

        if(self::$level > self::MAX_LEVEL) {
            throw new WrongSqlException('クエリビルダの WHERE の構造が複雑すぎます。');
        }
    }

    private function decrementLevel()
    {
        self::$level -= 1;
    }   

    public static function resetLevel()
    {
        self::$level = 0;
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