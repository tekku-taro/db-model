<?php
namespace Taro\DBModel\DB;

use PDO;
use Taro\DBModel\Exceptions\DatabaseConnectionException;
use Taro\DBModel\Utilities\FileHandler;

class DB
{
    /** @var PDO $dbh */
    private $dbh;

    /** @var DB $globalDb */
    private static $globalDb;

    public $connName;

    public $config;


    public function __construct($connName, $config, $dbh)
    {
        $this->connName = $connName;
        $this->config = $config;
        $this->dbh = $dbh;
    }

    private static function getDbhOrThrow(string $connName):PDO
    {
        $dbh = DbConnection::getConnection($connName);
        if($dbh === null) {
            throw new DatabaseConnectionException($connName.'と接続されていません。');
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

    public static function start(string $connName = null, bool $asGlobal = false): self
    {
        ['config'=>$config, 'connName'=>$connName] = self::loadConfig($connName);
        $dbh = DbConnection::open($connName, $config);
        $db = new self($connName, $config, $dbh);
        if($asGlobal) {
            self::$globalDb = $db;
        }
        return $db;
    }

    public function restart(): self
    {
        $this->stop();

        $dbh = DbConnection::open($this->connName, $this->config);
        $this->dbh = $dbh;
        return $this;
    }

    public static function restartGlobal(): self
    {
        self::$globalDb->stop();
        $db = DB::start(self::$globalDb->connName, true);
        return $db;
    }

    public function stop():void
    {
        DbConnection::close($this->connName);
    }

    public static function stopGlobal():void
    {
        self::$globalDb->stop();
    }

    public static function getGlobal():DB
    {
        return self::$globalDb;
    }

    public function getManipulator():DbManipulator
    {
        return new DbManipulator($this->dbh);
    }

    public function getPdo(): PDO
    {
        return $this->dbh;
    }

    public static function database(string $connName = null): self
    {
        ['config'=>$config, 'connName'=>$connName] = self::loadConfig($connName);
        $dbh = self::getDbhOrThrow($connName);
        return  new self($connName, $config, $dbh);
    }

    private static function loadConfig(string $connName = null): array
    {
        $config = FileHandler::loadConfig();
        if($connName === null) {
            $connName = $config['default'];
        }

        return ['config' =>$config['connections'][$connName], 'connName' => $connName];
    }

    public static function __callStatic($method, $args)
    {
        $class = get_called_class();
        if (is_callable([$class, $method])) {
            return (self::database(null))->$method(...$args);
        }        
    }

}