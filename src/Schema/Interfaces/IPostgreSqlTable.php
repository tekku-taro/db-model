<?php
namespace Taro\DBModel\Schema\Interfaces;

use Taro\DBModel\Schema\PostgreSql\Column\PostgreSqlColumn;
use Taro\DBModel\Schema\PostgreSql\Column\PostgreSqlForeignKey;
use Taro\DBModel\Schema\PostgreSql\Column\PostgreSqlIndex;

interface IPostgreSqlTable
{
    public function addColumn(string $name, string $columnType):PostgreSqlColumn;

    public function changeColumn(string $name,string $newName = null):PostgreSqlColumn;

    public function dropColumn(string $name);

    public function addForeign(string $column):PostgreSqlForeignKey;

    public function addIndex(...$columns):PostgreSqlIndex;
}
