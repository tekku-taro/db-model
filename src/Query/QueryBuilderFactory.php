<?php
namespace Taro\DBModel\Query;

use Taro\DBModel\DB\DbManipulator;
use Taro\DBModel\Query\Relations\RelationParams;

class QueryBuilderFactory
{
    public const HAS_MANY_RELATION = 'Taro\DBModel\Query\Relations\HasMany';
    public const HAS_ONE_RELATION = 'HasOne';
    public const BELONGS_TO_RELATION = 'BelongsTo';
    public const MANY_TO_MANY_RELATION = 'ManyToMany';
    public const HAS_MANY_THROUGH_RELATION = 'HasManyThrough';
    public const BELONGS_TO_THROUGH_RELATION = 'BelongsToThrough';

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