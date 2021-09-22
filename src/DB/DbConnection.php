<?php
namespace Taro\DBModel\DB;

use PDO;
use PDOException;

class DbConnection
{

    static private $dbhList = [];

    /**
     * @param string $dbName
     * @param array $config
     * @return PDO
     */
    public static function open(string $dbName, array $config):PDO
    {
        $dbh = self::getConnection($dbName);

        if($dbh !== null) {
            return $dbh;
        }

        try {
            // $dbh = new PDO('mysql:host=localhost;dbname=test', $user, $pass);
            $dsn = $config['driver'] . ':host=' . $config['host'] . ';dbname=' . $config['dbname'];
            $dbh = new PDO($dsn, $config['user'], $config['password']);
            self::$dbhList[$dbName] = $dbh;
            return $dbh;

        } catch (PDOException $e) {
            print "PDO接続エラー: " . $e->getMessage();
            die();
        }
    }

    /**
     * @param string $dbName
     * @return PDO|null
     */
    public static function getConnection(string $dbName):?PDO
    {
        if(isset(self::$dbhList[$dbName])) {
            return self::$dbhList[$dbName];
        }
    }

    /**
     * @param string $databaseName
     * @return void
     */
    public static function close(string $dbName):void
    {   
        if(isset(self::$dbhList[$dbName])) {
            self::$dbhList[$dbName] = null;
            unset(self::$dbhList[$dbName]);
        }
    }
}