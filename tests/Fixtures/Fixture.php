<?php
namespace Taro\Tests\Fixtures;

use Taro\DBModel\Exceptions\FixtureNotFoundException;
use Taro\DBModel\Query\DirectSql;
use Taro\DBModel\Utilities\Inflect;
use Taro\DBModel\Utilities\Str;

class Fixture
{
    public $tableName;

    public function fixTable(array $fixtureNameList)
    {
        if(empty($fixtureNameList)) {
            $fixtureNameList = ['default'];
        }
        foreach ($fixtureNameList as $fixtureName) {
            if(property_exists(static::class, $fixtureName) && !empty(static::$$fixtureName)) {
                DirectSql::query()->table($this->getTableName())->bulkInsert($this::$$fixtureName);            
            } else {
                throw new FixtureNotFoundException($fixtureName . 'というサンプルデータが見つかりませんでした。');
            }
        }
    }

    protected function getTableName()
    {
        if($this->tableName !== null) {
            return $this->tableName;
        }

        $className = Str::getShortClassName(static::class);
        $modelName = str_replace('Fixture', '', $className);
        return Inflect::pluralize(Str::snakeCase($modelName));
    }
}