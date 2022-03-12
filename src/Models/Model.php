<?php
namespace Taro\DBModel\Models;

use Taro\DBModel\DataMapping\DataMapper;
use Taro\DBModel\DB\DB;
use Taro\DBModel\DB\DbManipulator;
use Taro\DBModel\Exceptions\InvalidModelException;
use Taro\DBModel\Query\QueryBuilder;
use Taro\DBModel\Query\QueryBuilderFactory;
use Taro\DBModel\Query\Relations\BelongsTo;
use Taro\DBModel\Query\Relations\BelongsToThrough;
use Taro\DBModel\Query\Relations\HasMany;
use Taro\DBModel\Query\Relations\HasManyThrough;
use Taro\DBModel\Query\Relations\HasOne;
use Taro\DBModel\Query\Relations\ManyToMany;
use Taro\DBModel\Query\Relations\RelationBuilder;
use Taro\DBModel\Query\Relations\RelationParams;
use Taro\DBModel\Utilities\Inflect;
use Taro\DBModel\Utilities\Str;

class Model
{
    protected $id;

    protected $originals = [];

    protected $dirties = [];

    protected $fields = [];

    protected $dynamicFields = [];

    /** @var string|null 接続名 */
    protected static $database;

    protected $mapper;

    protected $deleted = false;

    public function __construct()
    {
        $dbManipulator = self::getDbManipulator();
        $this->mapper = new DataMapper($dbManipulator, static::class);
        $this->fields = $this->getFields();
    }



    public static function query(bool $useBindParam = true):QueryBuilder
    {
        $dbManipulator = self::getDbManipulator();
        $builder = new QueryBuilder($dbManipulator, static::class, $useBindParam);
        return $builder;        
    }


    protected static function getDbManipulator(): DbManipulator
    {
        if(static::$database === null) {
            return DB::getGlobal()->getManipulator();

        }
        return DB::database(static::$database)->getManipulator();
      
    }

    public function insert():string
    { 
        if(empty($this->dirties)) {
            throw new InvalidModelException(static::class . ' モデルに登録するデータがありません。');
        }

        $id = $this->mapper->executeInsert($this->dirties);
        $this->id = $id;
        $this->clearDirtiesAndUpdateOriginals($this->dirties);
        return $id;
    }

    public function update():bool
    {
        if($this->id === null) {
            throw new InvalidModelException(static::class . ' モデルのIDが不明です。');
        }
        if(empty($this->dirties)) {
            return true;
        }

        $result = $this->mapper->executeUpdate($this->id, $this->dirties);
        $this->clearDirtiesAndUpdateOriginals($this->dirties);
        return $result;
    }

    public function delete():bool
    {
        if($this->id === null) {
            throw new InvalidModelException(static::class . ' モデルのIDが不明です。');
        }

        $result = $this->mapper->executeDelete($this->id);

        return $this->deleted = $result;
    }

    
    protected function hasMany($modelName, $fKey = null, $relKey = 'id', bool $useBindParam = true): HasMany
    {   
        if($fKey === null) {
            $fKey = $this->getForeignKey(static::class);
        }
        $fkVal = $this->{$relKey};
        $params = new RelationParams([
            'fKey'=>$fKey,
            'fkVal'=>$fkVal,
            'modelName'=>$modelName,
            'relatedModelkey'=>$relKey,
        ]);

        return QueryBuilderFactory::createRelation(QueryBuilderFactory::HAS_MANY_RELATION, $this->getDbManipulator(), $modelName, $params, $useBindParam);
    }

    protected function belongsTo($modelName, $fKey = null, $relKey = 'id', bool $useBindParam = true): BelongsTo    
    {
        if($fKey === null) {
            $fKey = $this->getForeignKey($modelName);
        }        
        $pkVal = $this->{$fKey};
        $params = new RelationParams([
            'pKey'=>$relKey,
            'pkVal'=>$pkVal,
            'modelName'=>$modelName, 
            'relatedModelkey'=>$fKey,           
        ]);

        return QueryBuilderFactory::createRelation(QueryBuilderFactory::BELONGS_TO_RELATION, $this->getDbManipulator(), $modelName, $params, $useBindParam);

    }

    protected function hasOne($modelName, $fKey = null, $relKey = 'id', bool $useBindParam = true): HasOne    
    {
        if($fKey === null) {
            $fKey = $this->getForeignKey(static::class);
        }        
        $fkVal = $this->{$relKey};
        $params = new RelationParams([
            'fKey'=>$fKey,
            'fkVal'=>$fkVal,
            'modelName'=>$modelName,
            'relatedModelkey'=>$relKey, 
        ]);

        return QueryBuilderFactory::createRelation(QueryBuilderFactory::HAS_ONE_RELATION, $this->getDbManipulator(), $modelName, $params, $useBindParam);
    }

        protected function manyToMany($relatedModel, $pivoteTable, $pivoteFKey = null, $pivoteRelKey = null, $localKey = 'id', $relKey = 'id', bool $useBindParam = true): ManyToMany
        {
            if ($pivoteFKey === null) {
                $pivoteFKey = self::getForeignKey(static::class);
            }
            if ($pivoteRelKey === null) {
                $pivoteRelKey = self::getForeignKey($relatedModel);
            }
            $localVal = $this->{$localKey};
            $params = new RelationParams([
                'pKey' => $relKey,
                'fKey' => $pivoteRelKey,
                'relKey' => $pivoteFKey,
                'pivotTable' =>  $pivoteTable,
                'modelName' => $relatedModel,
                'relkVal' => $localVal,
                'relatedModelkey'=>$localKey, 
            ]); 
    
            return QueryBuilderFactory::createRelation(QueryBuilderFactory::MANY_TO_MANY_RELATION, $this->getDbManipulator(), $relatedModel, $params, $useBindParam);
        }

    protected function hasManyThrough($relatedModel, $middleModel, $middleFKey = null, $relatedFKey = null, $localKey = 'id', $middleLocalKey = 'id', bool $useBindParam = true): HasManyThrough
    {
        if ($middleFKey === null) {
            $middleFKey = self::getForeignKey(static::class);
        }
        if ($relatedFKey === null) {
            $relatedFKey = self::getForeignKey($middleModel);
        }
        $localVal = $this->{$localKey};
        $params = new RelationParams([
            'fKey' => $relatedFKey,
            'middleFKey' => $middleFKey,
            'middleLKey' => $middleLocalKey,
            'middleTable' => Inflect::pluralize(Str::snakeCase(Str::getShortClassName($middleModel))),
            'modelName' => $relatedModel,
            'relkVal' => $localVal,
            'relatedModelkey'=>$localKey, 
        ]); 

        return QueryBuilderFactory::createRelation(QueryBuilderFactory::HAS_MANY_THROUGH_RELATION, $this->getDbManipulator(), $relatedModel, $params, $useBindParam);

    }
    
    protected function belongsToThrough($relatedModel, $middleModel, $fKey = null, $middleFKey = null, $middleLocalKey = 'id', $relatedLocalKey = 'id', bool $useBindParam = true): BelongsToThrough
    {
        if ($fKey === null) {
            $fKey = self::getForeignKey($middleModel);
        }
        if ($middleFKey === null) {
            $middleFKey = self::getForeignKey($relatedModel);
        }
        $fkVal = $this->{$fKey};
        $params = new RelationParams([
            'pKey' => $relatedLocalKey,
            'middleFKey' => $middleFKey,
            'middleLKey' => $middleLocalKey,
            'middleTable' => Inflect::pluralize(Str::snakeCase(Str::getShortClassName($middleModel))),
            'modelName' => $relatedModel,
            'relkVal' => $fkVal,
            'relatedModelkey'=>$fKey, 
        ]); 

        return QueryBuilderFactory::createRelation(QueryBuilderFactory::BELONGS_TO_THROUGH_RELATION, $this->getDbManipulator(), $relatedModel, $params, $useBindParam);

    }


    protected static function getForeignKey($modelName)
    {
        return Str::snakeCase(Str::getShortClassName($modelName)) . '_id';
    }

    protected function dehydrate():array
    {
        
    }

    public function setDynamicProperty($name, $value):void
    {
        $this->dynamicFields[] = $name;
        $this->{$name} = $value;
    }


    public function __set($name, $value)    
    {
        return $this->setAndCheckDirty($name, $value);
    }

    public function __get($name)
    {
        if(isset($this->{$name})) {
            return $this->{$name};
        }
        if($name == 'id') {
            return $this->id;
        }
        return null;
    }

    protected function getFields():array
    {
        $reflectionClass = new \ReflectionClass($this);
        $baseClass = new \ReflectionClass(self::class);

        $basePropertyNames = array_map(function($property){
            return $property->getName();
        }, $baseClass->getProperties(\ReflectionProperty::IS_PROTECTED)) ;
        $fields = [];
        foreach ($reflectionClass->getProperties(\ReflectionProperty::IS_PROTECTED) as $property) {
            $propertyName = $property->getName();
            if(!in_array($propertyName, $basePropertyNames)) {
                $fields[] = $propertyName;
            }
        }  
        
        return $fields;
    }

    protected function setAndCheckDirty($field, $value):bool
    {
        if(in_array($field, $this->fields)){
            if (!isset($this->originals[$field]) || $value !== $this->originals[$field]) {
                $this->{$field} = $value;
                $this->dirties[$field] =$value;
                return true;
            }
        }
        if(in_array($field, $this->dynamicFields)){
            $this->{$field} = $value;
            return true;    
        }
        return false;
    }

    protected function setOriginals(array $record):void
    {
        $this->originals = $record;
    }

    protected function clearDirtiesAndUpdateOriginals(array $record):void
    {
        $this->dirties = [];
        $this->originals = array_merge($this->originals, $record);
    }

    public function fill(array $record):self
    {
        foreach ($record as $field => $value) {
            if(in_array($field, $this->fields)) {
                $this->setAndCheckDirty($field, $value);
            }
        }
        return $this;
    }

    public function initWith(array $record):self
    {
        $originals = [];
        foreach ($record as $field => $value) {
            if(in_array($field, $this->fields) || $field == 'id') {
                $this->{$field} = $value;                
                $originals[$field] = $value;
            } elseif ($field === RelationBuilder::MAP_KEY) {
                $this->dynamicFields[] = $field;
                $this->{$field} = $value;
            }
        }
        $this->setOriginals($originals);
        return $this;
    }
    
    protected function getPrimaryKeyVals():array    
    {

    }
}