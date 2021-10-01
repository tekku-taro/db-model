<?php
namespace Taro\DBModel\DB;

use PDO;
use PDOException;
use Taro\DBModel\Exceptions\DatabaseConnectionException;

class DbConnection
{

    static private $dbhList = [];

    /**
     * @param string $connName
     * @param array $config
     * @return PDO
     */
    public static function open(string $connName, array $config):PDO
    {
        $dbh = self::getConnection($connName);

        if($dbh !== null) {
            return $dbh;
        }

        try {
            // $dbh = new PDO('mysql:host=localhost;dbname=test', $user, $pass);
            $dsn = $config['driver'] . ':host=' . $config['host'] . ';dbname=' . $config['dbname'];
            $dbh = new PDO($dsn, $config['user'], $config['password']);
            $dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            self::$dbhList[$connName] = $dbh;
            return $dbh;

        } catch (PDOException $e) {
            throw new DatabaseConnectionException($connName.'に接続できませんでした。');
        }
    }

    /**
     * @param string $connName
     * @return PDO|null
     */
    public static function getConnection(string $connName):?PDO
    {
        if(isset(self::$dbhList[$connName])) {
            return self::$dbhList[$connName];
        }
        return null;
    }

    /**
     * @param string $connName
     * @return void
     */
    public static function close(string $connName):void
    {   
        if(isset(self::$dbhList[$connName])) {
            self::$dbhList[$connName] = null;
            unset(self::$dbhList[$connName]);
        }
    }

    /**
     * @param string $connName
     * @return void
     */
    public static function closeAll():void
    {   
        foreach (self::$dbhList as $dbh) {
            $dbh = null;
        }
        self::$dbhList = [];
    }
}