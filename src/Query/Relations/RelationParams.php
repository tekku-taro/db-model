<?php
namespace Taro\DBModel\Query\Relations;


class RelationParams
{
    public $pKey;

    public $fKey;    

    public $relKey;    

    public $pivotTable;    

    public $modelName;

    public $pkVal;  

    public $fkVal;    

    public $relkVal;

    function __construct(array $params)
    {
        foreach ($params as $name => $value) {
            if(property_exists($this, $name)) {
                $this->{$name} = $value;
            }
        }
    }

}