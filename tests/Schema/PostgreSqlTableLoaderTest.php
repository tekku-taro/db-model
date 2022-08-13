<?php

use PHPUnit\Framework\TestCase;
use Taro\DBModel\DB\DB;
use Taro\DBModel\Schema\DbDriver;
use Taro\DBModel\Schema\PostgreSql\PostgreSqlTable;
use Taro\DBModel\Schema\Schema;
use Taro\DBModel\Schema\Table;
use Taro\DBModel\Schema\TableLoading\TableFetcher;
use Taro\DBModel\Schema\TableLoading\TableLoader;

class PostgreSqlTableLoaderTest extends TestCase
{

    /** @var DB */
    private static $db;

    private static $tableName = 'test_loader';

    /** @var DbDriver */
    private $driver;

    public static function setUpBeforeClass():void
    {
        self::$db = DB::start('pgsql', true);
        Schema::createTable(self::$tableName, function(PostgreSqlTable $table){
            $table->addColumn('id','int')->primary();
            $table->addColumn('content','text')->nullable();
            $table->addColumn('status','string')->length(5)->default('good');
            $table->addColumn('user_id','int');
            
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
        $config = DB::getConfig('pgsql');
        $this->driver = new DbDriver($config);        
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
        
        $expected = "CREATE TABLE test_loader ( id INTEGER(32) NOT NULL,user_id INTEGER(32) NOT NULL,content TEXT,status CHARACTER VARYING(255) NOT NULL DEFAULT 'good',CONSTRAINT fk_test_loader_user_id_users_id FOREIGN KEY ( user_id ) REFERENCES users ( id ) ON DELETE CASCADE ON UPDATE NO ACTION,CONSTRAINT idx_test_loader_content_status UNIQUE ( content,status ),CONSTRAINT test_loader_pkey UNIQUE ( id ),PRIMARY KEY  ( id ) );";

        $this->assertEquals($expected, $sql);
    }

}