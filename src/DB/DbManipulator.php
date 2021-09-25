<?php
namespace Taro\DBModel\DB;

use PDO;
use PDOStatement;
use Taro\DBModel\Exceptions\WrongSqlException;

class DbManipulator
{

    private $dbh;

    public function __construct(PDO $dbh)
    {
        $this->dbh = $dbh;
    }

    public function execute($rawSql, $params = [], $options = []): array
    {
        $statement = $this->preExecute($rawSql, $params, $options);

        $result = $statement->execute();

        if ($result === false) {
            throw new \Exception($this->dbh->errorInfo()[2]);
        }        

        $result = $statement->fetchAll();
        $statement = null;
        return $result;
    }

    /**
     * @param PDOStatement $statement
     * @param array<string,mixed> $params [placeholder => $value]
     * @return void
     */
    private function bindParams(PDOStatement $statement, array $params):void
    {
        if(empty($params)) {
            return;
        }

        foreach ($params as $placeholder => $value) {
            $statement->bindValue($placeholder, $value, $this->getParamType($value));            
        }
    }

    private function getParamType($value): string
    {
        if(is_int($value)) {
            return PDO::PARAM_INT;
        }
        if(is_bool($value)) {
            return PDO::PARAM_BOOL;
        }

        return PDO::PARAM_STR;

    }



    public function executeAndStatement($rawSql, $params = [], $options = []): PDOStatement
    {
        $statement = $this->preExecute($rawSql, $params, $options);

        $result = $statement->execute();

        if ($result === false) {
            throw new \Exception($this->dbh->errorInfo()[2]);
        }        

        return $statement;
    }

    private function preExecute($rawSql, $params, $options = []): PDOStatement
    {
        $statement = $this->dbh->prepare($rawSql, $options);

        if($statement === false) {
            throw new WrongSqlException($rawSql);
        }

        $this->bindParams($statement, $params);

        return $statement;
    }

    public function executeAndBoolResult($rawSql, $params = [], $options = []): bool
    {
        $statement = $this->preExecute($rawSql, $params, $options);

        $result = $statement->execute();

        if ($result === false) {
            throw new \Exception($this->dbh->errorInfo()[2]);
        }        

        $statement = null;
        return $result;
    }

    public function getLastInsertedId():string
    {
        return $this->dbh->lastInsertId();
    }
}