<?php

use PHPUnit\Framework\TestCase;
use Taro\DBModel\DB\DB;
use Taro\DBModel\Query\DirectSql;
use Taro\DBModel\Schema\Schema;
use Taro\DBModel\Schema\Table;

class SchemaTest extends TestCase
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


    private function showTable(string $tableName)
    {
        $sql = 'SHOW CREATE TABLE ' . $tableName;
        $result = DirectSql::query()->prepareSql($sql)->runSql();
        return $result[0]['Create Table'];
    }

    private function tableExists(string $tableName):bool
    {
        $sql = 'SHOW TABLES';
        $result = DirectSql::query()->prepareSql($sql)->runSql();
        $tableNames = array_column($result, 'Tables_in_tasksdb');
        return in_array($tableName, $tableNames);
    }

    /**
     * @return void
     */
    public function testCreateTable()
    {
        Schema::createTable('test2', function(Table $table){
            $table->addColumn('id','int')->unsigned()->primary();
            $table->addColumn('content','text')->nullable();
            $table->addColumn('status','string')->length(5)->default('good');
            $table->addColumn('user_id','int')->unsigned();
            
            $table->addUnique('content', 'status');
            $table->addForeign('user_id')->references('users', 'id')->onDelete('CASCADE');
        });

        $sql = $this->showTable('test2');
        var_export($sql);
        $expected = 'CREATE TABLE `test2` (
  `id` int(10) unsigned NOT NULL,
  `content` text DEFAULT NULL,
  `status` varchar(5) NOT NULL DEFAULT \'good\',
  `user_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_test2_content_status` (`content`,`status`) USING HASH,
  KEY `fk_test2_user_id_users_id` (`user_id`),
  CONSTRAINT `fk_test2_user_id_users_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4';

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
        $table = Schema::getTable('test2');

        $table->addColumn('post_id','int');        
        $table->changeColumn('status')->default(0);
        $table->addForeign('post_id')->references('posts','id')->onDelete('cascade')->name('FK1');
        $table->dropForeign('fk_test2_user_id_users_id');
        $table->dropIndexByColumns('content','status');
        $table->addIndex('status')->name('INDEX1');        
        $table->dropColumn('content');

        Schema::alterTable($table);
          

        $sql = $this->showTable('test2');
        var_export($sql);
        $expected = "CREATE TABLE `test2` (  `id` int(10) unsigned NOT NULL,  `status` varchar(5) NOT NULL DEFAULT '0',  `user_id` int(10) unsigned NOT NULL,  `post_id` int(11) NOT NULL,  PRIMARY KEY (`id`),  KEY `FK1` (`post_id`),  KEY `INDEX1` (`status`),  CONSTRAINT `FK1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

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
        Schema::createTable('test2', function(Table $table){
            $table->addColumn('id','int')->unsigned()->primary();
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
}