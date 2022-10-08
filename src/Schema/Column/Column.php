<?php
namespace Taro\DBModel\Schema\Column;


abstract class Column
{
    public $name;
    
    public $tableName;

    public $length;

    public $precision;

    /** @var bool */
    public $nullable;

    protected $default;

    public $typeName;

    public $type;

    /** @var bool */
    protected $unsigned;


    /** @var string create/alter/drop  */
    protected $mode;

    /** @var string add/change/drop  */
    public $action;

    public const ADD_ACTION = 'ADD';
    public const CHANGE_ACTION = 'CHANGE';
    public const DROP_ACTION = 'DROP';    

    /** @var bool */
    public $isPk;

    /** @var bool */
    public $isUk;

    /** @var bool */
    public $autoIncrement;

    public $after;

    public $before;

    /** @var Column */
    public $original;    

    protected $rename;


    function __construct(string $action, string $name,string $type, string $tableName)
    {
        $this->action = $action;
        $this->name = $name;
        $this->tableName = $tableName;
        $this->typeName = $type;
        $this->constructType($type);
    }

    public function mode(string $mode):self
    {
        $this->mode = $mode;
        return $this;
    }


    public function rename(string $name):self
    {
        $this->rename = $name;
        return $this;
    }    

    protected function constructType(string $typeName)
    {
        $this->type($typeName);
    }

    abstract public function type(string $typeName):self;


    public function nullable(bool $mode = true):self
    {
        $this->nullable = $mode;
        return $this;
    }

    public function increment():self
    {

        $this->autoIncrement = true;
        $this->isPk = true;
        return $this;
    }

    public function primary(bool $mode = true):self
    {
        $this->isPk = $mode;
        return $this;
    }

    public function unique(bool $mode = true):self
    {
        $this->isUk = $mode;
        return $this;
    }

    public function default($defaultVal = null):self
    {
        $this->default = $defaultVal;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isChanged()
    {
        $changed = false;
        if($this->nullable != $this->original->nullable) {
            $changed = true;
        }
        if($this->default != $this->original->default) {
            $changed = true;
        }
        if($this->length != $this->original->length) {
            $changed = true;
        }
        if($this->typeName != $this->original->typeName) {
            $changed = true;
        }
        if($this->unsigned != $this->original->unsigned) {
            $changed = true;
        }
        if($this->after != $this->original->after) {
            $changed = true;
        }
        if($this->before != $this->original->before) {
            $changed = true;
        }

        return $changed;
    }

    abstract public function compile(): string;


}