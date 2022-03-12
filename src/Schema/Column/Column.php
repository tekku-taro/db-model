<?php
namespace Taro\DBModel\Schema\Column;

use Taro\DBModel\Exceptions\NotFoundException;
use Taro\DBModel\Schema\Column\ColumnType\ColumnTypeMap;

abstract class Column
{
    protected $name;

    protected $length;

    /** @var bool */
    protected $nullable;

    protected $default;

    protected $type;

    /** @var bool */
    protected $unsigned;


    /** @var string $mode create/alter/drop  */
    protected $mode;

    /** @var bool */
    protected $isPk;

    /** @var bool */
    protected $isUk;

    /** @var bool */
    protected $autoIncrement;

    protected $after;

    protected $before;

    function __construct(string $mode, string $name, string $type = 'string')
    {
        $this->mode = $mode;
        $this->name = $name;
        $this->type($type);
    }

    public function mode(string $mode):self
    {
        $this->mode = $mode;
        return $this;
    }

    public function type(string $typeName):self
    {
        if(ColumnTypeMap::includes($typeName)) {
            $this->type = ColumnTypeMap::getDBType($typeName);
        } else {
            throw new NotFoundException('利用できるカラムのデータ型に' . $typeName . 'というタイプはありません。');
        }
        return $this;
    }

    public function unsigned():self
    {
        $this->unsigned = true;
        return $this;
    }


    public function length(int $number):self
    {
        if(ColumnTypeMap::checkHasLength($this->type)) {
            $this->length = $number;
        } else {
            throw new NotFoundException('データ型:'.$this->type.'は最大文字数を設定できません。');
        }
        return $this;
    }

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