<?php
namespace Taro\DBModel\Schema\Interfaces;

use Taro\DBModel\Schema\Sqlite\Column\SqliteColumn;
use Taro\DBModel\Schema\Sqlite\Column\SqliteForeignKey;
use Taro\DBModel\Schema\Sqlite\Column\SqliteIndex;

interface ISqliteTable
{
    public function addColumn(string $name, string $columnType):SqliteColumn;

    public function changeColumn(string $name,string $newName = null):SqliteColumn;

    public function dropColumn(string $name);

    public function addForeign(string $column):SqliteForeignKey;

    public function addIndex(...$columns):SqliteIndex;
}
