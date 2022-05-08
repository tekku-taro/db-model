<?php

use PHPUnit\Framework\TestCase;
use Taro\DBModel\DB\DB;
use Taro\DBModel\Schema\DbDriver;
use Taro\DBModel\Schema\Sqlite\SqliteTable;
use Taro\DBModel\Schema\Schema;
use Taro\DBModel\Schema\Table;
use Taro\DBModel\Schema\TableLoading\TableFetcher;
use Taro\DBModel\Schema\TableLoading\TableLoader;

class SqliteTableLoaderTest extends TestCase
{

    /** @var DB */
    private static $db;

    private static $tableName = 'test_loader';

    /** @var DbDriver */
    private $driver;

    public static function setUpBeforeClass():void
    {
        self::$db = DB::start('sqlite', true);
        Schema::createTable(self::$tableName, function(SqliteTable $table){
            $table->addColumn('id','int')->primary();
            $table->addColumn('content','text')->nullable();
            $table->addColumn('status','string')->default('good');
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
        $config = DB::getConfig('sqlite');
        $this->driver = new DbDriver($config['driver']);        
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
        
        $expected = 'CREATE TABLE test_loader ( id INTEGER NOT NULL,content TEXT NOT NULL,status TEXT NOT NULL DEFAULT "good",user_id INTEGER NOT NULL,PRIMARY KEY  ( id ),CONSTRAINT fk_test_loader_user_id_users_id FOREIGN KEY ( user_id ) REFERENCES users ( id ) ON DELETE CASCADE );CREATE UNIQUE INDEX idx_test_loader_content_status ON test_loader ( content,status );';

        $this->assertEquals($expected, $sql);
    }

}
