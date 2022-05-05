<?php

use PHPUnit\Framework\TestCase;
use Taro\DBModel\DB\DbConnection;
use Taro\DBModel\Schema\Database;
use Taro\DBModel\Exceptions\DatabaseConnectionException;

class MySqlDatabaseTest extends TestCase
{

    private static $dbName1 = 'db_create_test';
    private static $connName1 = 'databaseCreateTest';
    private static $dbName2 = 'db_drop_test';
    private static $connName2 = 'databaseDropTest';

    public function setUp():void
    {
    }

    public function tearDown():void
    {
    }

    public static function tearDownAfterClass(): void
    {
        Database::dropIfExists(self::$dbName1, 'mysql');
        Database::dropIfExists(self::$dbName2, 'mysql');
    }

    private function getConfig($dbName)
    {
        return [
            'driver'=>'mysql',
            'host'=>'localhost',
            'user'=>'root',
            'password'=>'',
            'dbname'=>$dbName,
        ];
        
    }


    /**
     * @return void
     */
    public function testCreate()
    {
        Database::create(self::$dbName1, 'utf8', 'mysql');

        $dbh = DbConnection::open(self::$connName1, $this->getConfig(self::$dbName1));

        $this->assertInstanceOf(PDO::class, $dbh);     

        DbConnection::close(self::$connName1);
    }


    /**
     * @return void
     */
    public function testDrop()
    {
        $this->expectException(DatabaseConnectionException::class);

        Database::create(self::$dbName2, 'utf8');
        
        Database::dropIfExists(self::$dbName2);
        
        // db削除後で、接続エラー発生する
        $dbh = DbConnection::open(self::$connName2, $this->getConfig(self::$dbName2));
    }


}