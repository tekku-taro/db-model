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
            $table->addForeign('user_id')->references('users', ['id'])->onDelete('CASCADE');
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

    private static function trimLineBreaks(string $string)
    {
        return preg_replace( "/\r|\n/", "", $string );
    }

    /**
     * @return void
     */
    public function testDropTable()
    {
        Schema::dropTableIfExists('test2');

        $result = $this->tableExists('test2');
        $this->assertFalse($result);        
    }

}