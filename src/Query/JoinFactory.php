<?php
namespace Taro\DBModel\Query;

use Taro\DBModel\Query\Joins\Join;

class JoinFactory
{
    public const JOIN =       'Taro\DBModel\Query\Joins\Join';
    public const LEFT_JOIN =  'Taro\DBModel\Query\Joins\LeftJoin';
    public const RIGHT_JOIN = 'Taro\DBModel\Query\Joins\RightJoin';
    public const OUTER_JOIN = 'Taro\DBModel\Query\Joins\OuterJoin';

    public $mergedQuery;


    public static function create($joinType, $leftTable):Join
    {
        // new Join($table);
        return new $joinType($leftTable);
    }
}