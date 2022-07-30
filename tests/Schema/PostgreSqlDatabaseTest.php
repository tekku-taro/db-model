<?php

use PHPUnit\Framework\TestCase;
use Taro\DBModel\DB\DbConnection;
use Taro\DBModel\Schema\Database;
use Taro\DBModel\Exceptions\DatabaseConnectionException;

class PostgreSqlDatabaseTest extends TestCase
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
        Database::dropIfExists(self::$dbName1, 'pgsql');
        Database::dropIfExists(self::$dbName2, 'pgsql');
    }

    private function getConfig($dbName)
    {
        return [
            'driver'=>'pgsql',
            'host'=>'localhost',
            'user'=>'postgres',
            'password'=>'password',
            'dbname'=>$dbName,
            'port'=>5433,
        ];
        
    }


    /**
     * @return void
     */
    public function testCreate()
    {
        Database::create(self::$dbName1, 'UTF8', 'pgsql');

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

        Database::create(self::$dbName2, 'UTF8', 'pgsql');
        
        Database::dropIfExists(self::$dbName2);
        
        // db削除後で、接続エラー発生する
        $dbh = DbConnection::open(self::$connName2, $this->getConfig(self::$dbName2));
    }


}