<?php
namespace Taro\DBModel\Schema\Interfaces;

use Taro\DBModel\Schema\MySql\Column\MySqlColumn;
use Taro\DBModel\Schema\MySql\Column\MySqlForeignKey;
use Taro\DBModel\Schema\MySql\Column\MySqlIndex;

interface IMySqlTable
{
    public function addColumn(string $name, string $columnType):MySqlColumn;

    public function changeColumn(string $name,string $newName = null):MySqlColumn;

    public function dropColumn(string $name);

    public function addForeign(string $column):MySqlForeignKey;

    public function addIndex(...$columns):MySqlIndex;
}
