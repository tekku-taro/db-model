<?php
namespace Taro\DBModel\Traits;

trait ParamsTrait
{

    protected static $incrementedParamNo = 0;

    private $useBindParam = true;


    public function bindParam($paramName, $value):self
    {
        $this->query->params[$paramName] = $value;
        return $this;        
    }

    protected function placeholdersForRecord($record): array
    {
        $values = array_map(function($value) {
            return $this->replacePlaceholder($value);
        }, $record);

        return $values;
    }

    
    
    protected function replacePlaceholder($value):string
    {
        if($this->useBindParam && $value !== null) {
            if($value[0] === ':') {
                return $value;
            }
            $placeholder = $this->generatePlaceholder();
            $this->bindParam($placeholder, $value);
            return $placeholder;
        }

        return $this->parseTypes($value);

    }  

    protected function parseTypes($value)
    {
        if(is_null($value)) {
            return 'NULL';
        }
        if(is_numeric($value) || is_bool($value)) {
            return $value;
        }
        return '"' . $value . '"';
    }

    protected function generatePlaceholder(): string
    {
        self::$incrementedParamNo += 1;
        return ':param'. self::$incrementedParamNo;
    }
}