<?php
namespace Taro\DBModel\DB;

use PDO;
use Taro\DBModel\Exceptions\DatabaseNotConnectedException;
use Taro\DBModel\Utilities\FileHandler;

class DB
{
    /** @var PDO $dbh */
    private $dbh;

    public $dbName;

    public $config;


    public function __construct($dbName, $config, $dbh)
    {
        $this->dbName = $dbName;
        $this->config = $config;
        $this->dbh = $dbh;
    }

    private static function getDbhOrThrow(string $dbName):PDO
    {
        $dbh = DbConnection::getConnection($dbName);
        if($dbh === null) {
            throw new DatabaseNotConnectedException($dbName);
        }
        return $dbh; 
    }

    public function beginTrans(): bool
    {
        return $this->dbh->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->dbh->commit();
    }

    public function rollback(): bool
    {
        return $this->dbh->rollback();
    }

    public static function start(string $dbName = null): self
    {
        ['config'=>$config, 'dbName'=>$dbName] = self::loadConfig($dbName);
        $dbh = DbConnection::open($dbName, $config);
        return new self($dbName, $config, $dbh);
    }

    public function restart(): self
    {
        $this->stop();

        $dbh = DbConnection::open($this->dbName, $this->config);
        return $this;
    }

    public function stop()
    {
        DbConnection::close($this->dbName);
    }

    public function getManipulator()
    {
        return new DbManipulator($this->dbh);
    }

    public function getPdo(): PDO
    {
        return $this->dbh;
    }

    public static function database(string $dbName = null): self
    {
        ['config'=>$config, 'dbName'=>$dbName] = self::loadConfig($dbName);
        $dbh = self::getDbhOrThrow($dbName);
        return  new self($dbName, $config, $dbh);
    }

    private static function loadConfig(string $dbName = null): array
    {
        $config = FileHandler::loadConfig();
        if($dbName === null) {
            $dbName = $config['default'];
        }

        return ['config' =>$config['dbList'][$dbName], 'dbName' => $dbName];
    }

    public static function __callStatic($method, $args)
    {
        $class = get_called_class();
        if (is_callable([$class, $method])) {
            return (self::database(null))->$method(...$args);
        }        
    }

}