<?php
namespace Taro\DBModel\DataMapping;

use Taro\DBModel\DB\DbManipulator;
use Taro\DBModel\Traits\ParamsTrait;
use Taro\DBModel\Traits\SqlBaseTrait;
use Taro\DBModel\Utilities\Inflect;
use Taro\DBModel\Utilities\Str;

class DataMapper
{
    use ParamsTrait;
    use SqlBaseTrait;
    
    private $params;

    public $modelName;

    public $table;

    private $dbManipulator;

    private $useBindParam = true;

    public function __construct(DbManipulator $dbManipulator, $modelName)
    {
        $this->dbManipulator = $dbManipulator;
        $this->modelName = $modelName;
        $this->table = $this->table = Inflect::pluralize(Str::snakeCase(Str::getShortClassName($this->modelName)));
    }    

    public function executeInsert($record)
    {
        $sql = $this->prepareInsert($record);
        if($this->dbManipulator->executeAndBoolResult($sql, $this->params)) {
            return $this->dbManipulator->getLastInsertedId();
        }
        return false;
    }

    public function executeUpdate($id, $record):bool
    {
        $sql = $this->prepareUpdate($record);
        $sql .= ' WHERE id = ' . $this->replacePlaceholder($id) . ' ;';

        return $this->dbManipulator->executeAndBoolResult($sql, $this->params);    
    }

    public function executeDelete($id):bool
    {
        $sql = $this->prepareDelete();
        $sql .= 'WHERE id = ' . $this->replacePlaceholder($id) . ' ;';

        return $this->dbManipulator->executeAndBoolResult($sql, $this->params);  
    }

}