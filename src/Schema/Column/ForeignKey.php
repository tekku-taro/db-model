<?php
namespace Taro\DBModel\Schema\Column;


abstract class ForeignKey
{
    /** @var array<string> */
    public $columnNames = [];

    public $name;

    protected $onDelete;

    protected $onUpdate;

    protected $referencedTable;

    /** @var array<string> */    
    protected $referencedColumns = [];


    /** @var string $mode create/alter/drop  */
    protected $mode;


    /** @var string add/drop  */
    protected $action;

    public const ADD_ACTION = 'ADD';
    public const DROP_ACTION = 'DROP';   


    /** @var ForeignKey */
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


    /**
     * @param string $table
     * @param array<string> $columns
     * @return self
     */
    public function references(string $table,array $columns):self
    {
        $this->referencedTable = $table;
        $this->referencedColumns = $columns;
        $this->name = $this->generateFkName();
        return $this;
    }

    public function name(string $name):self  
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param string $mode  SET NULL|CASCADE|RESTRICT|NO ACTION|SET DEFAULT
     * @return self
     */
    public function onDelete(string $mode = 'CASCADE'):self  
    {
        $this->onDelete = $mode;
        return $this;
    }

    /**
     * @param string $mode  SET NULL|CASCADE|RESTRICT|NO ACTION|SET DEFAULT
     * @return self
     */
    public function onUpdate(string $mode = 'CASCADE'):self
    {
        $this->onUpdate = $mode;
        return $this;
    }

    abstract public function compile(): string;


    public function generateFkName(): string    
    {
        return 'fk_' . implode("_", $this->columnNames)  . '_' . $this->referencedTable . '_' . implode("_", $this->referencedColumns);
    }

}