<?php
namespace Taro\DBModel\Schema\Column;

use Taro\DBModel\Exceptions\NotFoundException;
use Taro\DBModel\Schema\Column\ColumnType\ColumnTypeMap;

abstract class Column
{
    public $name;
    
    public $tableName;

    protected $length;

    /** @var bool */
    protected $nullable;

    protected $default;

    public $typeName;

    public $type;

    /** @var bool */
    protected $unsigned;


    /** @var string create/alter/drop  */
    protected $mode;

    /** @var string add/change/drop  */
    protected $action;

    public const ADD_ACTION = 'ADD';
    public const CHANGE_ACTION = 'CHANGE';
    public const DROP_ACTION = 'DROP';    

    /** @var bool */
    public $isPk;

    /** @var bool */
    protected $isUk;

    /** @var bool */
    public $autoIncrement;

    protected $after;

    protected $before;

    /** @var Column */
    public $original;    

    protected $rename;


    function __construct(string $action, string $name, string $type = 'string',string $tableName)
    {
        $this->action = $action;
        $this->name = $name;
        $this->tableName = $tableName;
        $this->typeName = $type;
        $this->type($type);
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

    abstract public function type(string $typeName):self;

    public function unsigned():self
    {
        $this->unsigned = true;
        return $this;
    }


    abstract public function length(int $number):self;

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

    public function after(string $columnName):self
    {
        $this->after = $columnName;
        return $this;
    }

    public function before(string $columnName):self
    {
        $this->before = $columnName;        
        return $this;
    }

    abstract public function compile(): string;


}