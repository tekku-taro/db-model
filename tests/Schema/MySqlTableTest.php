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
        self::$db = DB::start('mysql', true);        
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
        $table->addForeign('user_id')->references('users', 'id')->onDelete('CASCADE');

        $sql = $table->generateSql(Table::CREATE_MODE);
        var_export($sql);
        
        $expected = 'CREATE TABLE test ('.
        ' id INT UNSIGNED NOT NULL,'.
        'content TEXT,'.
        'status VARCHAR(5) NOT NULL DEFAULT "good",'.
        'user_id INT UNSIGNED NOT NULL,'.
        'FOREIGN KEY fk_test_user_id_users_id ( user_id ) REFERENCES users ( id ) ON DELETE CASCADE,'.
        'UNIQUE idx_test_content_status ( content,status ),'.
        'PRIMARY KEY  ( id )'.
        ' );';

        $this->assertEquals($expected, $sql);
    }

    /**
     * @return void
     */
    public function testGenerateAlterTable()
    {
        Schema::createTable('test', function(Table $table){
            $table->addColumn('id','int')->unsigned()->primary();
            $table->addColumn('content','text')->nullable();
            $table->addColumn('status','string')->length(5)->default('good');
            $table->addColumn('user_id','int')->unsigned();
            
            $table->addUnique('content', 'status');
            $table->addForeign('user_id')->references('users', 'id')->onDelete('CASCADE');
        }); 

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
        
        $expected = 'ALTER TABLE test DROP FOREIGN KEY fk_test_user_id_users_id;ALTER TABLE test DROP INDEX fk_test_user_id_users_id;ALTER TABLE test DROP INDEX idx_test_content_status;ALTER TABLE test ADD COLUMN post_id INT NOT NULL;ALTER TABLE test CHANGE COLUMN status status VARCHAR(5) NOT NULL DEFAULT "0";ALTER TABLE test DROP COLUMN content;ALTER TABLE test ADD FOREIGN KEY FK1 ( post_id ) REFERENCES posts ( id ) ON DELETE cascade;ALTER TABLE test ADD INDEX INDEX1 ( status );';

        $this->assertEquals($expected, $sql);   
        
        
    }

    /**
     * @return void
     */
    public function testGenerateDropTable()
    {
        Schema::createTable('test', function(Table $table){
            $table->addColumn('id','int')->unsigned()->primary();
        }); 

        $table = Schema::getTable('test');
        
        Schema::dropTableIfExists('test');

        $sql = $table->generateSql(Table::DROP_MODE);
        var_export($sql);
        
        $expected = 'DROP TABLE test;';

        $this->assertEquals($expected, $sql);        
    }

}