<?php
namespace Taro\DBModel\Query\Clauses;

use Taro\DBModel\Exceptions\WrongSqlException;

class WhBlockRow implements WhClauseInterface
{
    public $conjunct;
    
    /** @var array<WhClauseInterface> $rowBlocks */
    public $rowBlocks = [];

    private static $level = 0;
    
    private const MAX_LEVEL = 6;


    public function addAnd(WhClauseInterface $block)
    {
        $block->setConjunct('AND');
        $this->rowBlocks[] = $block;
    }

    public function addOr(WhClauseInterface $block)
    {
        $block->setConjunct('OR');
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
        // if($conjunct === 'AND') {
        //     return '( ' . $sqlA . ' ) ' . $conjunct . ' ( ' . $sqlB . ' )';        
        // }
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
    

    public function getConjunct(): string
    {
        return $this->conjunct;
    }
    
    public function setConjunct(string $value)
    {
        $this->conjunct = $value;
    }    
}