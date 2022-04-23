<?php
namespace Taro\DBModel\Schema\Column;


abstract class ForeignKey
{
    /** @var string */
    public $columnName;

    public $name;

    protected $onDelete;

    protected $onUpdate;

    public $referencedTable;

    public $tableName;

    /** @var string */    
    public $referencedColumn;


    /** @var string $mode create/alter/drop  */
    protected $mode;


    /** @var string add/drop  */
    public $action;

    public const ADD_ACTION = 'ADD';
    public const DROP_ACTION = 'DROP';   


    /** @var ForeignKey */
    public $original;  


    function __construct(string $action,string $columnName, string $tableName)
    {
        $this->action = $action;
        $this->columnName = $columnName;
        $this->tableName = $tableName;
    }
    
    public function mode(string $mode):self
    {
        $this->mode = $mode;
        return $this;
    }


    /**
     * @param string $table
     * @param string $column
     * @return self
     */
    public function references(string $table,string $column):self
    {
        $this->referencedTable = $table;
        $this->referencedColumn = $column;
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
        return 'fk_' . $this->tableName . '_' . $this->columnName . '_' . $this->referencedTable . '_' . $this->referencedColumn;
    }

}