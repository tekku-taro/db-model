<?php
namespace Taro\DBModel\Schema\Column;


abstract class ForeignKey
{
    /** @var array<string> */
    protected $columnNames = [];

    protected $fkName;

    protected $onDelete;

    protected $onUpdate;

    protected $referencedTable;

    protected $referencedColumn;


    /** @var string $mode create/alter/drop  */
    protected $mode;


    function __construct(...$columnNames)
    {
        $this->columnNames = $columnNames;
    }
    
    public function mode(string $mode):self
    {
        $this->mode = $mode;
        return $this;
    }



    public function references(string $table,string $column):self
    {
        $this->referencedTable = $table;
        $this->referencedColumn = $column;
        $this->fkName = $this->generateFkName();
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


    protected function generateFkName(): string    
    {
        return 'fk_' . implode("_", $this->columnNames)  . '_' . $this->referencedTable . '_' . $this->referencedColumn;
    }

}