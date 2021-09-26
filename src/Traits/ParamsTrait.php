<?php
namespace Taro\DBModel\Traits;


trait ParamsTrait
{

    protected static $incrementedParamNo = 0;


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
        if($this->useBindParam) {
            if($value[0] === ':') {
                return $value;
            }
            $placeholder = $this->generatePlaceholder();
            $this->bindParam($placeholder, $value);
            return $placeholder;
        }
        return '"' . $value . '"';

    }  

    protected function generatePlaceholder(): string
    {
        self::$incrementedParamNo += 1;
        return ':param'. self::$incrementedParamNo;
    }
}