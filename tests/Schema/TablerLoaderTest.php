<?php

use PHPUnit\Framework\TestCase;
use Taro\DBModel\DB\DB;
use Taro\DBModel\Schema\DbDriver;
use Taro\DBModel\Schema\Schema;
use Taro\DBModel\Schema\Table;
use Taro\DBModel\Schema\TableLoading\TableFetcher;
use Taro\DBModel\Schema\TableLoading\TableLoader;

class TablerLoaderTest extends TestCase
{

    /** @var DB */
    private static $db;

    private static $tableName = 'test_loader';

    /** @var DbDriver */
    private $driver;

    public static function setUpBeforeClass():void
    {
        self::$db = DB::start('mysql', true);
        Schema::createTable(self::$tableName, function(Table $table){
            $table->addColumn('id','int')->unsigned()->primary();
            $table->addColumn('content','text')->nullable();
            $table->addColumn('status','string')->length(5)->default('good');
            $table->addColumn('user_id','int')->unsigned();
            
            $table->addUnique('content', 'status');
            $table->addForeign('user_id')->references('users', 'id')->onDelete('CASCADE');
        });        
    }

    public static function tearDownAfterClass():void
    {
        Schema::dropTableIfExists(self::$tableName);
        self::$db->stop();
    }

    public function setUp():void
    {
        $config = DB::getConfig('mysql');
        $this->driver = new DbDriver($config['driver'],$config['dbname']);        
    }


    /**
     * @return void
     */
    public function testLoad()
    {
        /** @var TableFetcher $fetcher */
        $fetcher = TableFetcher::fetchInfo(self::$tableName, $this->driver, DB::getGlobal()->getManipulator());

        $loader = new TableLoader(self::$tableName, $this->driver, $fetcher);
        /** @var Table $table */
        $table = $loader->load();
        
        $this->assertInstanceOf(Table::class, $table);

        
        $sql = $table->generateSql(Table::CREATE_MODE);
        var_export($sql);
        
        $expected = 'CREATE TABLE test_loader ( id INT(10) UNSIGNED NOT NULL,content TEXT,status VARCHAR(5) NOT NULL DEFAULT "good",user_id INT(10) UNSIGNED NOT NULL,FOREIGN KEY fk_test_loader_user_id_users_id ( user_id ) REFERENCES users ( id ),UNIQUE idx_test_loader_content_status ( content,status ),INDEX fk_test_loader_user_id_users_id ( user_id ),PRIMARY KEY  ( id ) );';

        $this->assertEquals($expected, $sql);
    }

}