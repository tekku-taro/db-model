<?php
namespace Taro\DBModel\Query\Joins;


class Join
{
    protected $leftTable;

    protected $rightTable;

    protected $leftKey;

    protected $rightKey;
    
    protected $type = 'INNER JOIN';


    public function __construct(string $leftTable)
    {
        $this->leftTable = $leftTable;
    }

    public function joinTable(string $rightTable):self  
    {
        $this->rightTable = $rightTable;
        return $this;
    }
    
    public function on($leftKey, $rightKey):self  
    {
        $this->leftKey = $leftKey;
        $this->rightKey = $rightKey;
        return $this;
    }

    public function toSql(): string
    {
        $sql = ' ' . $this->type . ' ' . $this->rightTable . ' ON ( ' . 
            $this->leftTable . '.' . $this->leftKey . ' = ' .
            $this->rightTable . '.' . $this->rightKey . ' ) ';

        return $sql;
    }
}