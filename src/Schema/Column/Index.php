<?php
namespace Taro\DBModel\Schema\Column;


abstract class Index
{
    /** @var array<string> */
    public $columnNames = [];
    
    public $name;

    public $tableName;

    /** @var bool */
    public $unique;

    /** @var string create/alter/drop  */
    protected $mode;

    /** @var string add/drop  */
    public $action;

    public const ADD_ACTION = 'ADD';
    public const DROP_ACTION = 'DROP';   


    /** @var Index */
    public $original;  

    function __construct(string $action, $columnNames = [],string $tableName)
    {
        $this->action = $action;
        $this->columnNames = $columnNames;
        $this->tableName = $tableName;
        $this->name = $this->generateIdkName();
    }
    

    public function name(string $name):self  
    {
        $this->name = $name;
        return $this;
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

    abstract public function compile();


    protected function generateIdkName(): string    
    {
        return 'idx_' . $this->tableName  . '_' . implode("_", $this->columnNames);
    }

}