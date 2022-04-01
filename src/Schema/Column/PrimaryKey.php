<?php
namespace Taro\DBModel\Schema\Column;


abstract class PrimaryKey
{
    /** @var array<string> */
    public $columnNames = [];

    /** @var string $mode create/alter/drop  */
    protected $mode;


    /** @var string add/drop  */
    protected $action;

    public const ADD_ACTION = 'ADD';
    public const DROP_ACTION = 'DROP';   


    /** @var PrimaryKey */
    public $original;  


    function __construct(string $action, $columnNames = [])
    {
        $this->action = $action;
        $this->columnNames = $columnNames;
    }
    
    public function mode(string $mode):self
    {
        $this->mode = $mode;
        return $this;
    }

    public function addColumns(array $columns)
    {
        $this->columnNames[] += $columns;
    }

    abstract public function compile(): string;
}