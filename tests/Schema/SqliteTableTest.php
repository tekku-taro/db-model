<?php

use PHPUnit\Framework\TestCase;
use Taro\DBModel\DB\DB;
use Taro\DBModel\Exceptions\WrongSqlException;
use Taro\DBModel\Schema\Schema;
use Taro\DBModel\Schema\Sqlite\SqliteTable;
use Taro\DBModel\Schema\Table;

class SqliteTableTest extends TestCase
{

    /** @var DB */
    private static $db;

    public static function setUpBeforeClass():void
    {
        self::$db = DB::start('sqlite', true);        
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
        $table = new SqliteTable('test');
        $table->addColumn('id','int')->primary();
        $table->addColumn('content','text')->nullable();
        $table->addColumn('status','string')->default('good');
        $table->addColumn('user_id','int');
        
        $table->addUnique('content', 'status');
        $table->addForeign('user_id')->references('users', 'id')->onDelete('CASCADE');

        $sql = $table->generateSql(Table::CREATE_MODE);
        var_export($sql);
        
        $expected = 'CREATE TABLE test ( id INTEGER NOT NULL,content TEXT,status TEXT NOT NULL DEFAULT "good",user_id INTEGER NOT NULL,PRIMARY KEY  ( id ),CONSTRAINT fk_test_user_id_users_id FOREIGN KEY ( user_id ) REFERENCES users ( id ) ON DELETE CASCADE );CREATE UNIQUE INDEX idx_test_content_status ON test ( content,status );';

        $this->assertEquals($expected, $sql);
    }

    /**
     * @return void
     */
    public function testGenerateAlterTable()
    {
        Schema::createTable('test', function(SqliteTable $table){
            $table->addColumn('id','int')->primary();
            $table->addColumn('content','text')->nullable();
            $table->addColumn('status','string')->default('good');
            $table->addColumn('user_id','int');
            $table->addColumn('post_id','int'); 
            $table->addUnique('content', 'status');
            $table->addForeign('user_id')->references('users', 'id')->onDelete('CASCADE');
        }); 

        /** @var SqliteTable $table */
        $table = Schema::getTable('test');

        Schema::dropTableIfExists('test');
      
        $table->changeColumn('status')->default(0);
        $table->addForeign('post_id')->references('posts','id')->onDelete('cascade')->name('FK1');
        $table->dropForeignKeyByColumn('user_id');
        $table->dropIndex('idx_test_content_status');
        $table->addIndex('status')->name('INDEX1');        
        $table->dropColumn('content');
        
        $sql = $table->generateSql(Table::ALTER_MODE);
        var_export($sql);
        
        $expected = 'PRAGMA foreign_keys=off;BEGIN TRANSACTION;ALTER TABLE test RENAME TO ___old_test;CREATE TABLE test ( id INTEGER NOT NULL,status TEXT NOT NULL DEFAULT "0",user_id INTEGER NOT NULL,post_id INTEGER NOT NULL,PRIMARY KEY  ( id ),CONSTRAINT fk_test_user_id_users_id FOREIGN KEY ( user_id ) REFERENCES users ( id ) ON DELETE CASCADE,CONSTRAINT FK1 FOREIGN KEY ( post_id ) REFERENCES posts ( id ) ON DELETE cascade );INSERT INTO test SELECT id,status,user_id,post_id FROM ___old_test;DROP TABLE ___old_test;CREATE INDEX INDEX1 ON test ( status );COMMIT;PRAGMA foreign_keys=on;';
  
        $this->assertEquals($expected, $sql);   
        
        
    }

    /**
     * @return void
     */
    public function testGenerateDropTable()
    {
        Schema::createTable('test', function(SqliteTable $table){
            $table->addColumn('id','int')->primary();
        }); 

        $table = Schema::getTable('test');
        
        Schema::dropTableIfExists('test');

        $sql = $table->generateSql(Table::DROP_MODE);
        var_export($sql);
        
        $expected = 'DROP TABLE test;';

        $this->assertEquals($expected, $sql);        
    }

    /**
     * @return void
     */
    public function testAddPrimaryKey()
    {
        $table = new SqliteTable('test');
        $table->addColumn('id','int');
        $table->addColumn('task','string');

        $table->addPrimaryKey('id', 'task');

        $sql = $table->generateSql(Table::CREATE_MODE);
        var_export($sql);
        
        $expected = 'CREATE TABLE test ( id INTEGER NOT NULL,task TEXT NOT NULL,PRIMARY KEY  ( id,task ) );';

        $this->assertEquals($expected, $sql);      
    }

    /**
     * @return void
     */
    public function testChangePrimaryKey()
    {
        Schema::createTable('test', function(SqliteTable $table){
            $table->addColumn('id','int');
            $table->addColumn('task','string');
    
            $table->addPrimaryKey('id', 'task');
        }); 

        $table = Schema::getTable('test');
        
        Schema::dropTableIfExists('test');

        $table->dropPrimaryKey();
        $table->addPrimaryKey('task');

        $sql = $table->generateSql(Table::ALTER_MODE);
        var_export($sql);
        
        $expected = 'PRAGMA foreign_keys=off;BEGIN TRANSACTION;ALTER TABLE test RENAME TO ___old_test;CREATE TABLE test ( id INTEGER NOT NULL,task TEXT NOT NULL,PRIMARY KEY  ( task ) );INSERT INTO test SELECT id,task FROM ___old_test;DROP TABLE ___old_test;COMMIT;PRAGMA foreign_keys=on;';

        $this->assertEquals($expected, $sql);     
    }

 

    /**
     * @expectedException WrongSqlException
     */
    public function testValidateNullable()
    {
        $this->expectException(WrongSqlException::class);
        Schema::createTable('test', function(SqliteTable $table){
            $table->addColumn('id','int')->nullable();
            $table->addColumn('task','string');
    
            $table->addPrimaryKey('id');
        });    
    }          
}
