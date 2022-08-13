<?php

use PHPUnit\Framework\TestCase;
use Taro\DBModel\DB\DB;
use Taro\DBModel\Exceptions\WrongSqlException;
use Taro\DBModel\Schema\PostgreSql\PostgreSqlTable;
use Taro\DBModel\Schema\Schema;
use Taro\DBModel\Schema\Table;

class PostgreSqlTableTest extends TestCase
{

    /** @var DB */
    private static $db;

    public static function setUpBeforeClass():void
    {
        self::$db = DB::start('pgsql', true);        
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
        $table = new PostgreSqlTable('test');
        $table->addColumn('id','int')->primary();
        $table->addColumn('content','text')->nullable();
        $table->addColumn('status','string')->default('good');
        $table->addColumn('user_id','int');
        
        $table->addUnique('content', 'status');
        $table->addForeign('user_id')->references('users', 'id')->onDelete('CASCADE');

        $sql = $table->generateSql(Table::CREATE_MODE);
        // var_export($sql);
        
        $expected = "CREATE TABLE test ( id INTEGER NOT NULL,content TEXT,status CHARACTER VARYING(255) NOT NULL DEFAULT 'good',user_id INTEGER NOT NULL,CONSTRAINT fk_test_user_id_users_id FOREIGN KEY ( user_id ) REFERENCES users ( id ) ON DELETE CASCADE,CONSTRAINT idx_test_content_status UNIQUE ( content,status ),PRIMARY KEY  ( id ) );";

        $this->assertEquals($expected, $sql);
    }

    /**
     * @return void
     */
    public function testGenerateAlterTable()
    {
        Schema::createTable('test', function(PostgreSqlTable $table){
            $table->addColumn('id','int')->primary();
            $table->addColumn('content','text')->nullable();
            $table->addColumn('status','string')->length(5)->default('good');
            $table->addColumn('user_id','int');
            
            $table->addUnique('content', 'status');
            $table->addForeign('user_id')->references('users', 'id')->onDelete('CASCADE');
        }); 

        /** @var PostgreSqlTable $table */
        $table = Schema::getTable('test');

        Schema::dropTableIfExists('test');

        $table->addColumn('post_id','int');        
        $table->changeColumn('status')->default(0);
        $table->addForeign('post_id')->references('posts','id')->onDelete('cascade')->name('FK1');
        $table->dropForeignKeyByColumn('user_id');
        $table->dropIndex('idx_test_content_status');
        $table->addIndex('status')->name('INDEX1');        
        $table->dropColumn('content');

        $sql = $table->generateSql(Table::ALTER_MODE);
        var_export($sql);
        
        $expected = "ALTER TABLE test DROP CONSTRAINT fk_test_user_id_users_id;ALTER TABLE test DROP CONSTRAINT IF EXISTS idx_test_content_status;DROP INDEX IF EXISTS idx_test_content_status;ALTER TABLE test ADD COLUMN post_id INTEGER NOT NULL;ALTER TABLE test ALTER COLUMN status SET DEFAULT '0';ALTER TABLE test DROP COLUMN content;ALTER TABLE test ADD CONSTRAINT FK1 FOREIGN KEY ( post_id ) REFERENCES posts ( id ) ON DELETE cascade;CREATE INDEX INDEX1 ON test ( status );";

        $this->assertEquals($expected, $sql);   
        
        
    }

    /**
     * @return void
     */
    public function testGenerateDropTable()
    {
        Schema::createTable('test', function(PostgreSqlTable $table){
            $table->addColumn('id','int')->primary();
        }); 

        $table = Schema::getTable('test');
        
        Schema::dropTableIfExists('test');

        $sql = $table->generateSql(Table::DROP_MODE);
        // var_export($sql);
        
        $expected = 'DROP TABLE test;';

        $this->assertEquals($expected, $sql);        
    }

    /**
     * @return void
     */
    public function testAddPrimaryKey()
    {
        $table = new PostgreSqlTable('test');
        $table->addColumn('id','int');
        $table->addColumn('task','string');

        $table->addPrimaryKey('id', 'task');

        $sql = $table->generateSql(Table::CREATE_MODE);
        // var_export($sql);
        
        $expected = 'CREATE TABLE test ( id INTEGER NOT NULL,task CHARACTER VARYING(255) NOT NULL,PRIMARY KEY  ( id,task ) );';

        $this->assertEquals($expected, $sql);      
    }

    /**
     * @return void
     */
    public function testChangePrimaryKey()
    {
        Schema::createTable('test', function(PostgreSqlTable $table){
            $table->addColumn('id','int');
            $table->addColumn('task','string');
    
            $table->addPrimaryKey('id', 'task');
        }); 

        $table = Schema::getTable('test');
        
        Schema::dropTableIfExists('test');

        $table->dropPrimaryKey();
        $table->addPrimaryKey('task');

        $sql = $table->generateSql(Table::ALTER_MODE);
        // var_export($sql);
        
        $expected = 'ALTER TABLE test DROP CONSTRAINT test_pkey;ALTER TABLE test ADD CONSTRAINT test_pkey PRIMARY KEY  ( task );';

        $this->assertEquals($expected, $sql);     
    }

 

    /**
     * @expectedException WrongSqlException
     */
    public function testValidateNullable()
    {
        $this->expectException(WrongSqlException::class);
        Schema::createTable('test', function(PostgreSqlTable $table){
            $table->addColumn('id','int')->nullable();
            $table->addColumn('task','string');
    
            $table->addPrimaryKey('id');
        });    
    }          
}
