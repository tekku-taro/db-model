<?php
namespace Taro\DBModel\Schema\Column;


abstract class Index
{
    /** @var array<string> */
    protected $columnNames = [];
    
    protected $idxName;

    /** @var bool */
    protected $unique;

    /** @var string create/alter/drop  */
    protected $mode;

    function __construct(...$columnNames)
    {
        $this->columnNames = $columnNames;
        $this->idxName = $this->generateIdkName();
    }
    
    public function unique(bool $unique):self
    {
        $this->unique = $unique;
        return $this;
    }
    
    public function mode(string $mode):self
    {
        $this->mode = $mode;
        return $this;
    }

    abstract public function compile(): string;


    protected function generateIdkName(): string    
    {
        return 'idx_' . implode("_", $this->columnNames);
    }

}