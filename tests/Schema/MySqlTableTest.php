<?php

use PHPUnit\Framework\TestCase;
use Taro\DBModel\DB\DB;
use Taro\DBModel\Schema\MySql\MySqlTable;
use Taro\DBModel\Schema\Schema;
use Taro\DBModel\Schema\Table;

class MySqlTableTest extends TestCase
{

    /** @var DB */
    private static $db;

    public static function setUpBeforeClass():void
    {
        self::$db = DB::start('mysql');
    }

    public static function tearDownAfterClass():void
    {
        self::$db->stop();
    }


    /**
     * @return void
     */
    public function testGenerateCreateTable()
    {
        $table = new MySqlTable('test');
        $table->addColumn('id','int')->unsigned()->primary();
        $table->addColumn('content','text')->nullable();
        $table->addColumn('status','string')->length(5)->default('good');
        $table->addColumn('user_id','int')->unsigned();
        
        $table->addUnique('content', 'status');
        $table->addForeign('user_id')->references('users', ['id'])->onDelete('CASCADE');

        $sql = $table->generateSql(Table::CREATE_MODE);
        var_export($sql);
        
        $expected = 'CREATE TABLE test ('.
        ' id INT UNSIGNED NOT NULL,'.
        'content TEXT,'.
        'status VARCHAR(5) NOT NULL DEFAULT "good",'.
        'user_id INT UNSIGNED NOT NULL,'.
        'FOREIGN KEY fk_user_id_users_id ( user_id ) REFERENCES users ( id ) ON DELETE CASCADE,'.
        'UNIQUE idx_content_status ( content,status ),'.
        'PRIMARY KEY  ( id )'.
        ' );';

        $this->assertEquals($expected, $sql);
    }

    /**
     * @return void
     */
    public function testGenerateAlterTable()
    {
    }

    /**
     * @return void
     */
    public function testGenerateDropTable()
    {
    }

}