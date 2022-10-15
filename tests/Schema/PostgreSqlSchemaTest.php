<?php

use PHPUnit\Framework\TestCase;
use Taro\DBModel\DB\DB;
use Taro\DBModel\Query\DirectSql;
use Taro\DBModel\Schema\PostgreSql\PostgreSqlTable;
use Taro\DBModel\Schema\Schema;
use Taro\DBModel\Schema\Table;

class PostgreSqlSchemaTest extends TestCase
{

    /** @var DB */
    private static $db;

    public static function setUpBeforeClass():void
    {
        self::$db = DB::start('pgsql', true);
        Schema::dropTableIfExists('test2');
    }

    public static function tearDownAfterClass():void
    {
        self::$db->stop();
    }


    private function tableExists(string $tableName):bool
    {
        $sql = "SELECT table_name
        FROM information_schema.tables
        WHERE table_schema = 'public'
        ORDER BY table_name;";
        $result = DirectSql::query()->prepareSql($sql)->runSql();
        $tableNames = array_column($result, 'table_name');
        return in_array($tableName, $tableNames);
    }

    /**
     * @return void
     */
    public function testCreateTable()
    {
        $table = Schema::createTable('test2', function(PostgreSqlTable $table){
            $table->addColumn('id','int')->increment();
            $table->addColumn('content','text')->nullable();
            $table->addColumn('status','string')->length(5)->default('good');
            $table->addColumn('user_id','int');
            
            $table->addUnique('content', 'status');
            $table->addForeign('user_id')->references('users', 'id')->onDelete('CASCADE');
        });

        $expected = "CREATE TABLE test2 ( id SERIAL NOT NULL,user_id INTEGER(32) NOT NULL,content TEXT,status CHARACTER VARYING(5) NOT NULL DEFAULT 'good',CONSTRAINT fk_test2_user_id_users_id FOREIGN KEY ( user_id ) REFERENCES users ( id ) ON DELETE CASCADE ON UPDATE NO ACTION,CONSTRAINT idx_test2_content_status UNIQUE ( content,status ),CONSTRAINT test2_pkey UNIQUE ( id ),PRIMARY KEY  ( id ) );";

        $this->assertEquals(self::trimLineBreaks($expected), self::trimLineBreaks($table->original->generateSql(Table::CREATE_MODE)));    
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
        /** @var PostgreSqlTable $table */
        $table = Schema::getTable('test2');

        $table->addColumn('post_id','int');        
        $table->changeColumn('status')->default(0);
        $table->addForeign('post_id')->references('posts','id')->onDelete('cascade')->name('FK1');
        $table->dropForeign('fk_test2_user_id_users_id');
        $table->dropIndexByColumns('content','status');
        $table->addIndex('status')->name('INDEX1');        
        $table->dropColumn('content');

        $alteredTable = Schema::alterTable($table);
          
        $expected = "CREATE TABLE test2 ( id SERIAL NOT NULL,user_id INTEGER(32) NOT NULL,post_id INTEGER(32) NOT NULL,status CHARACTER VARYING(5) NOT NULL DEFAULT '0',CONSTRAINT fk_test2_post_id_posts_id FOREIGN KEY ( post_id ) REFERENCES posts ( id ) ON DELETE CASCADE ON UPDATE NO ACTION,CONSTRAINT test2_pkey UNIQUE ( id ),PRIMARY KEY  ( id ) );CREATE INDEX index1 ON test2 ( status );";

        $this->assertEquals(self::trimLineBreaks($expected), self::trimLineBreaks($alteredTable->original->generateSql(Table::CREATE_MODE)));
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
        Schema::createTable('test2', function(PostgreSqlTable $table){
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
        $table = Schema::saveTable('test2', function(PostgreSqlTable $table){
            $table->addColumn('id','int')->increment();
            $table->addColumn('content','text')->nullable();
            $table->addColumn('status','string')->length(5)->default('good');
            $table->addColumn('user_id','int');
            
            $table->addUnique('content', 'status');
            $table->addForeign('user_id')->references('users', 'id')->onDelete('CASCADE');
        });

        $expected = "CREATE TABLE test2 ( id SERIAL NOT NULL,user_id INTEGER(32) NOT NULL,content TEXT,status CHARACTER VARYING(5) NOT NULL DEFAULT 'good',CONSTRAINT fk_test2_user_id_users_id FOREIGN KEY ( user_id ) REFERENCES users ( id ) ON DELETE CASCADE ON UPDATE NO ACTION,CONSTRAINT idx_test2_content_status UNIQUE ( content,status ),CONSTRAINT test2_pkey UNIQUE ( id ),PRIMARY KEY  ( id ) );";

        $this->assertEquals(self::trimLineBreaks($expected), self::trimLineBreaks($table->original->generateSql(Table::CREATE_MODE)));    


        $alteredTable = Schema::saveTable('test2', function(PostgreSqlTable $table){
            $table->addColumn('id','int')->primary();
            $table->addColumn('status','string')->length(5)->default(0);
            $table->addColumn('user_id','int');
            $table->addColumn('post_id','int');        

            $table->addForeign('post_id')->references('posts','id')->onDelete('cascade')->name('FK1');

            $table->addIndex('status')->name('INDEX1');  
        });

        $expected = "CREATE TABLE test2 ( id SERIAL NOT NULL,user_id INTEGER(32) NOT NULL,post_id INTEGER(32) NOT NULL,status CHARACTER VARYING(5) NOT NULL DEFAULT '0',CONSTRAINT fk_test2_post_id_posts_id FOREIGN KEY ( post_id ) REFERENCES posts ( id ) ON DELETE CASCADE ON UPDATE NO ACTION,CONSTRAINT test2_pkey UNIQUE ( id ),PRIMARY KEY  ( id ) );CREATE INDEX index1 ON test2 ( status );";

        $this->assertEquals(self::trimLineBreaks($expected), self::trimLineBreaks($alteredTable->original->generateSql(Table::CREATE_MODE)));


        Schema::dropTableIfExists('test2');
    }     
}
