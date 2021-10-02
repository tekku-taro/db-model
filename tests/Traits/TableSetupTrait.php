<?php
namespace Taro\Tests\Traits;

use Taro\DBModel\DB\DB;
use Taro\DBModel\Query\DirectSql;
use Taro\DBModel\Utilities\Str;
use Taro\Tests\Fixtures\Fixture;

trait TableSetupTrait
{
    private $connName = 'mysql';

    private function clearTable($tableName)
    {
        $sql = 'DELETE FROM ' . $tableName . ' WHERE 1 = 1;';
        $dbh = $this->db->getPdo();
        $stmt = $dbh->query($sql);

    }

    private function setupConnection()
    {
        $this->db = DB::start($this->connName, true);
    }

    private function seeInDatabase($table, $data)
    {
        $sql = 'SELECT count(*) FROM ' . $table . ' WHERE ';
        foreach ($data as $key => $value) {
            $whereClause[] = $key . ' = "' . $value . '"';
        }

        $sql .= implode(' AND ', $whereClause);

        $dbh = $this->db->getPdo();

        $stmt = $dbh->query($sql);
        if ($stmt->fetchColumn() > 0) {
            return true;
        }
        return false;
    }

    private function fillTable($tableName, $fixutureList = [])
    {
        $fixture = $this->getFixture($tableName);

        return $fixture->fixTable($fixutureList);
    }

    private function getFixture($tableName): Fixture
    {
        $fixtureClass = 'Taro\\Tests\\Fixtures\\' . Str::pascalCase($tableName) . 'Fixture';
        return new $fixtureClass;
    }
}