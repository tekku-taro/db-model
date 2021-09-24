<?php

use PHPUnit\Framework\TestCase;
use Taro\DBModel\DB\DB;
use Taro\DBModel\DB\DirectSql;
use Taro\DBModel\Models\Post;

class DirectSqlTest extends TestCase
{
    private $connName = 'mysql';

    /** @var DB $db */
    private $db;

    private $sampleData = [
        ['title'=>'test1', 'views'=>'5', 'finished'=>0, 'hidden'=>'secret' ],
        ['title'=>'test2', 'views'=>'4', 'finished'=>1, 'hidden'=>'secret' ],
        ['title'=>'test3', 'views'=>'1', 'finished'=>1, 'hidden'=>'public' ],
        [ 'title'=>'test4', 'views'=>'2', 'finished'=>1, 'hidden'=>'public' ],
        [ 'title'=>'test5', 'views'=>'3', 'finished'=>0, 'hidden'=>'public' ],
    ];

    public function setUp():void
    {
        $this->setupConnection();
        // $this->adapter->delete('posts', []);
        $this->fillTable('posts');
    }

    public function tearDown():void
    {
        $this->clearTable('posts');   
        $this->db->stop();
    }

    private function clearTable($tableName)
    {
        $sql = 'DELETE FROM ' . $tableName . ' WHERE 1 = 1;';
        $dbh = $this->db->getPdo();
        $stmt = $dbh->query($sql);

    }

    private function setupConnection()
    {
        $this->db = DB::start($this->connName, true);
    }

    private function seeInDatabase($table, $data)
    {
        $sql = 'SELECT count(*) FROM ' . $table . ' WHERE ';
        foreach ($data as $key => $value) {
            $whereClause[] = $key . ' = "' . $value . '"';
        }

        $sql .= implode(' AND ', $whereClause);

        $dbh = $this->db->getPdo();

        $stmt = $dbh->query($sql);
        if ($stmt->fetchColumn() > 0) {
            return true;
        }
        return false;
    }

    private function fillTable($tableName)
    {
        DirectSql::query()->table($tableName)->bulkInsert($this->sampleData);
    }

    public function testInsert()
    {
        $this->clearTable('posts');
        DirectSql::query()->table('posts')->insert($this->sampleData[0]);

        $this->assertTrue($this->seeInDatabase('posts', $this->sampleData[0]));
    }

    public function testBulkInsert()
    {
        $this->clearTable('posts');
        DirectSql::query()->table('posts')->bulkInsert($this->sampleData);
        
        $failures = array_filter($this->sampleData, function($record) {
            return !$this->seeInDatabase('posts', $record);
        });

        $this->assertEquals(0, count($failures));
    }


    public function testPrepareAndRunSql()
    {
        $sql = 'SELECT * FROM posts WHERE title = :title';
        $query = DirectSql::query()->prepareSql($sql);
        $query->bindParam(':title', 'test1');
        $results = $query->runSql();

        $expected = $this->sampleData[0];
        // print_r($results);
        $this->assertEquals($expected, array_intersect_key($results[0],$expected));
    }

    public function testGetAsArray()
    {
        $query = DirectSql::query()->table('posts');

        $results = $query->select('title','finished')
            ->getAsArray();

        $expected = [
            array (
              'title' => 'test1',
              'finished' => 0,
            ),
            array (
              'title' => 'test2',
              'finished' => 1,
            ),
            array (
              'title' => 'test3',
              'finished' => 1,
            ),
            array (
              'title' => 'test4',
              'finished' => 1,
            ),
            array (
              'title' => 'test5',
              'finished' => 0,
            ),
        ];
        $this->assertTrue(is_array($results));
        $this->assertEquals($expected, $results);
    }

    public function testGetAsModels()
    {
        $query = DirectSql::query()->table('posts');

        $results = $query->select('title','finished')
            ->getAsModels(Post::class);

        $expected = [
            array (
              'title' => 'test1',
              'finished' => 0,
            ),
            array (
              'title' => 'test2',
              'finished' => 1,
            ),
            array (
              'title' => 'test3',
              'finished' => 1,
            ),
            array (
              'title' => 'test4',
              'finished' => 1,
            ),
            array (
              'title' => 'test5',
              'finished' => 0,
            ),
        ];
        $this->assertTrue(is_array($results));
        $this->assertInstanceOf(Post::class, $results[0]);
        $this->assertEquals('test1', $results[0]->title);
    }

    public function testWhere()
    {
        $posts = DirectSql::query()->table('posts')
            ->where('title', 'test1')
            ->getAsModels(Post::class);

        $expected = 'test1';

        $this->assertEquals($expected, $posts[0]->title);

        $posts = DirectSql::query()->table('posts')
            ->where('title', 'IN', ['test1', 'test2'])
            ->getAsModels(Post::class);

        $expected = ['test1', 'test2'];
        $this->assertCount(2, $posts);
        $this->assertEquals($expected, [$posts[0]->title,$posts[1]->title]);

        $posts = DirectSql::query()->table('posts')
            ->where('views', '>', '2')
            ->getAsModels(Post::class);

        $this->assertCount(3, $posts);
    }

    public function testOrderBy()
    {
        $posts = DirectSql::query()->table('posts')
            ->orderBy('views', 'DESC')
            ->getAsArray();        

        $expected = ['test1','test2','test5','test4','test3'];

        $this->assertEquals($expected, array_column($posts, 'title'));
    }

    public function testLimit()
    {
        $posts = DirectSql::query()->table('posts')
            ->limit(2)
            ->getAsArray();                
        $expected = 2;

        $this->assertCount($expected, $posts);
    }



    public function testUpdate()
    {
        $updateData =[
            'title' => 'test1-1',
            'body' => 'test post 1-1'
        ];
        DirectSql::query()->table('posts')
            ->where('title', ':title')->bindParam(':title', 'test1')
            ->update($updateData);

        $this->assertTrue($this->seeInDatabase('posts', $updateData));
    }

    public function testDelete()
    {
        $title = 'test1';

        DirectSql::query()->table('posts')
            ->where('title', $title)
            ->delete();

        $this->assertFalse($this->seeInDatabase('posts', ['title'=>$title]));
    }
}