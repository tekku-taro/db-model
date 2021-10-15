<?php
namespace Taro\DBModel\Query;

use Taro\DBModel\DB\DbManipulator;
use Taro\DBModel\Query\Relations\RelationParams;

class QueryBuilderFactory
{
    public const HAS_MANY_RELATION = 'Taro\DBModel\Query\Relations\HasMany';
    public const HAS_ONE_RELATION = 'Taro\DBModel\Query\Relations\HasOne';
    public const BELONGS_TO_RELATION = 'Taro\DBModel\Query\Relations\BelongsTo';
    public const MANY_TO_MANY_RELATION = 'Taro\DBModel\Query\Relations\ManyToMany';
    public const HAS_MANY_THROUGH_RELATION = 'Taro\DBModel\Query\Relations\HasManyThrough';
    public const BELONGS_TO_THROUGH_RELATION = 'Taro\DBModel\Query\Relations\BelongsToThrough';

    public $queryBuilder;

    public $mergedQuery;


    public static function createRelation($relationType, DbManipulator $dbManipulator, $modelName, RelationParams $params, bool $useBindParam):QueryBuilder
    {
        // new HasMany($params, $dbManipulator, false);
        return new $relationType($params, $dbManipulator, $useBindParam);
    }

    public function mergeQuery():QueryBuilder
    {

    }
}