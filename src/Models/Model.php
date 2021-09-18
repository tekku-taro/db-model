<?php
namespace Taro\DBModel\Models;

use Taro\DBModel\Query\QueryBuilder;
use Taro\DBModel\Query\Relations\BelongsTo;
use Taro\DBModel\Query\Relations\BelongsToThrough;
use Taro\DBModel\Query\Relations\HasMany;
use Taro\DBModel\Query\Relations\HasManyThrough;
use Taro\DBModel\Query\Relations\HasOne;
use Taro\DBModel\Query\Relations\ManyToMany;

class Model
{
    public $primaryKeys;

    public $originals;

    public $dirties;

    public $table;

    public function query():QueryBuilder
    {

    }

    public function insert():bool    
    {

    }

    public function update():bool    
    {

    }

    public function delete():bool
    {

    }

    
    protected function hasMany(): HasMany
    {

    }

    protected function belongsTo(): BelongsTo    
    {

    }

    protected function hasOne(): HasOne    
    {

    }

    protected function manyToMany(): ManyToMany    
    {

    }

    protected function belongsToThrough(): BelongsToThrough
    {

    }

    protected function hasManyThrough(): HasManyThrough
    {

    }

    
    private function dehydrate():array
    {
        
    }

    public function __set($name, $value)    
    {

    }

    public function __get($name)
    {

    }

    
    private function getPrimaryKeyVals():array    
    {

    }
}