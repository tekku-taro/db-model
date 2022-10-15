<?php

use PHPUnit\Framework\TestCase;
use Taro\DBModel\DB\DB;
use Taro\DBModel\Query\DirectSql;
use Taro\DBModel\Schema\Schema;
use Taro\DBModel\Schema\Sqlite\SqliteTable;
use Taro\DBModel\Schema\Table;

class SqliteSchemaTest extends TestCase
{

    /** @var DB */
    private static $db;

    public static function setUpBeforeClass():void
    {
        self::$db = DB::start('sqlite', true);
        Schema::dropTableIfExists('test2');
    }

    public static function tearDownAfterClass():void
    {
        self::$db->stop();
    }


    private function showTable(string $tableName)
    {
        $sql = 'SELECT sql FROM sqlite_master WHERE tbl_name = "' . $tableName . '" AND type = "table";';
        $result = DirectSql::query()->prepareSql($sql)->runSql();
        return $result[0]['sql'];
    }

    private function showIndex(string $tableName)
    {
        $sql = 'SELECT sql FROM sqlite_master 
        WHERE tbl_name = "'.$tableName.'"
        AND type = "index"
        ;';
        $result = DirectSql::query()->prepareSql($sql)->runSql();
        return $result[0]['sql'];
    }

    private function tableExists(string $tableName):bool
    {
        $sql = 'SELECT name FROM sqlite_master WHERE type="table" ORDER BY name;';
        $result = DirectSql::query()->prepareSql($sql)->runSql();
        $tableNames = array_column($result, 'name');
        return in_array($tableName, $tableNames);
    }

    /**
     * @return void
     */
    public function testCreateTable()
    {
        Schema::createTable('test2', function(SqliteTable $table){
            $table->addColumn('id','int')->primary();
            $table->addColumn('content','text')->nullable();
            $table->addColumn('status','string')->default('good');
            $table->addColumn('user_id','int');
            
            $table->addUnique('content', 'status');
            $table->addForeign('user_id')->references('users', 'id')->onDelete('CASCADE');
        });

        $sql = $this->showTable('test2');
        var_export($sql);
        $expected = 'CREATE TABLE test2 ( id INTEGER NOT NULL,content TEXT,status TEXT NOT NULL DEFAULT "good",user_id INTEGER NOT NULL,PRIMARY KEY  ( id ),CONSTRAINT fk_test2_user_id_users_id FOREIGN KEY ( user_id ) REFERENCES users ( id ) ON DELETE CASCADE )';

        $this->assertEquals(self::trimLineBreaks($expected), self::trimLineBreaks($sql));

        $sql = $this->showIndex('test2');
        var_export($sql);
        $expected = "CREATE UNIQUE INDEX idx_test2_content_status ON test2 ( content,status )";
        $this->assertEquals(self::trimLineBreaks($expected), self::trimLineBreaks($sql));        
    }




    /**
     * @return void
     */
    public function testGetTable()
    {
        $table = Schema::getTable('test2');

        $this->assertInstanceOf(Table::class, $table);

        $this->assertEquals('test2', $table->name); 
    }


    /**
     * @return void
     */
    public function testAlterTable()
    {
        /** @var SqliteTable $table */
        $table = Schema::getTable('test2');
        
        $table->changeColumn('status')->default(0);
        $table->addColumn('post_id','int'); 
        $table->addForeign('post_id')->references('posts','id')->onDelete('cascade')->name('FK1');
        $table->dropForeign('fk_test2_user_id_users_id');
        $table->dropIndexByColumns('content','status');
        $table->addIndex('status')->name('INDEX1');        
        $table->dropColumn('content');

        Schema::alterTable($table);
          

        $sql = $this->showTable('test2');
        var_export($sql);
        $expected = 'CREATE TABLE test2 ( id INTEGER NOT NULL,status TEXT NOT NULL DEFAULT "0",user_id INTEGER NOT NULL,post_id INTEGER NOT NULL,PRIMARY KEY  ( id ),CONSTRAINT fk_test2_user_id_users_id FOREIGN KEY ( user_id ) REFERENCES users ( id ) ON DELETE CASCADE,CONSTRAINT FK1 FOREIGN KEY ( post_id ) REFERENCES posts ( id ) ON DELETE cascade )';
        $this->assertEquals(self::trimLineBreaks($expected), self::trimLineBreaks($sql));
        $sql = $this->showIndex('test2');
        var_export($sql);
        $expected = "CREATE INDEX INDEX1 ON test2 ( status )";
        $this->assertEquals(self::trimLineBreaks($expected), self::trimLineBreaks($sql));

    }


    /**
     * @return void
     */
    public function testDropTableIfExists()
    {
        Schema::dropTableIfExists('test2');

        $result = $this->tableExists('test2');
        $this->assertFalse($result);        
    }

    /**
     * @return void
     */
    public function testDropTable()
    {
        Schema::createTable('test2', function(SqliteTable $table){
            $table->addColumn('id','int')->primary();
        });

        $table = Schema::getTable('test2');

        Schema::dropTable($table);

        $result = $this->tableExists('test2');
        $this->assertFalse($result);        
    }


    
    private static function trimLineBreaks(string $string)
    {
        return preg_replace( "/\r|\n/", "", $string );
    }

    /**
     * @group failing
     * @return void
     */
    public function testSaveTable()
    {
        Schema::saveTable('test2', function(SqliteTable $table){
            $table->addColumn('id','int')->primary();
            $table->addColumn('content','text')->nullable();
            $table->addColumn('status','string')->default('good');
            $table->addColumn('user_id','int');
            $table->addColumn('post_id','int');

            $table->addUnique('content', 'status');
            $table->addForeign('user_id')->references('users', 'id')->onDelete('CASCADE');
        });

        $sql = $this->showTable('test2');
        var_export($sql);
        $expected = 'CREATE TABLE test2 ( id INTEGER NOT NULL,content TEXT,status TEXT NOT NULL DEFAULT "good",user_id INTEGER NOT NULL,post_id INTEGER NOT NULL,PRIMARY KEY  ( id ),CONSTRAINT fk_test2_user_id_users_id FOREIGN KEY ( user_id ) REFERENCES users ( id ) ON DELETE CASCADE )';

        $this->assertEquals(self::trimLineBreaks($expected), self::trimLineBreaks($sql));

        $sql = $this->showIndex('test2');
        var_export($sql);
        $expected = "CREATE UNIQUE INDEX idx_test2_content_status ON test2 ( content,status )";
        $this->assertEquals(self::trimLineBreaks($expected), self::trimLineBreaks($sql));

        Schema::saveTable('test2', function(SqliteTable $table){ 
            $table->addColumn('id','int')->primary();
            $table->addColumn('status','string')->default(0);
            $table->addColumn('user_id','int');   
            $table->addColumn('post_id','int');
            
            $table->addIndex('status')->name('INDEX1');
            $table->addForeign('post_id')->references('posts','id')->onDelete('cascade')->name('FK1');
        });

        $sql = $this->showTable('test2');
        var_export($sql);
        $expected = 'CREATE TABLE test2 ( id INTEGER NOT NULL,status TEXT NOT NULL DEFAULT "0",user_id INTEGER NOT NULL,post_id INTEGER NOT NULL,PRIMARY KEY  ( id ),CONSTRAINT fk_test2_user_id_users_id FOREIGN KEY ( user_id ) REFERENCES users ( id ) ON DELETE CASCADE,CONSTRAINT FK1 FOREIGN KEY ( post_id ) REFERENCES posts ( id ) ON DELETE cascade )';
        $this->assertEquals(self::trimLineBreaks($expected), self::trimLineBreaks($sql));
        $sql = $this->showIndex('test2');
        var_export($sql);
        $expected = "CREATE INDEX INDEX1 ON test2 ( status )";
        $this->assertEquals(self::trimLineBreaks($expected), self::trimLineBreaks($sql));

        Schema::dropTableIfExists('test2');
    }        
}
