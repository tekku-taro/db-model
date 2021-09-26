<?php

use PHPUnit\Framework\TestCase;
use Taro\DBModel\DB\DB;
use Taro\DBModel\Query\DirectSql;
use Taro\DBModel\Models\Post;
use Taro\DBModel\Query\QueryBuilder;

class QueryBuilderTest extends TestCase
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
        QueryBuilder::query(Post::class)->insert($this->sampleData[0]);

        $this->assertTrue($this->seeInDatabase('posts', $this->sampleData[0]));
    }

    public function testBulkInsert()
    {
        $this->clearTable('posts');
        QueryBuilder::query(Post::class)->bulkInsert($this->sampleData);
        
        $failures = array_filter($this->sampleData, function($record) {
            return !$this->seeInDatabase('posts', $record);
        });

        $this->assertEquals(0, count($failures));
    }

    public function testGetFirst()
    {
        $query = QueryBuilder::query(Post::class);
        $result = $query->select('title','finished')
            ->orderBy('title', 'ASC')
            ->getFirst();

        $expected = 'test1';
        $this->assertInstanceOf(Post::class, $result);     
        $this->assertEquals($expected, $result->title);
    }

    public function testGetAll()
    {
        $query = QueryBuilder::query(Post::class);

        $results = $query->select('title','finished')
            ->getAll();

        $this->assertTrue(is_array($results));
        $this->assertInstanceOf(Post::class, $results[0]);
        $this->assertEquals('test1', $results[0]->title);
    }

    public function testWhere()
    {
        $posts = QueryBuilder::query(Post::class)
            ->where('title', 'test1')
            ->getAll();

        $expected = 'test1';

        $this->assertEquals($expected, $posts[0]->title);

        $posts = QueryBuilder::query(Post::class)
            ->where('title', 'IN', ['test1', 'test2'])
            ->getAll();

        $expected = ['test1', 'test2'];
        $this->assertCount(2, $posts);
        $this->assertEquals($expected, [$posts[0]->title,$posts[1]->title]);

        $posts = QueryBuilder::query(Post::class)
            ->where('views', '>', '2')
            ->getAll();

        $this->assertCount(3, $posts);
    }

    public function testBindParam()
    {
        $posts = QueryBuilder::query(Post::class)
            ->where('title', ':title1')->bindParam(':title1', 'test1')
            ->getAll();

        $expected = 'test1';

        $this->assertEquals($expected, $posts[0]->title);
    }

    public function testOrderBy()
    {
        $posts = QueryBuilder::query(Post::class)
            ->orderBy('views', 'DESC')
            ->getAll();        

        $expected = ['test1','test2','test5','test4','test3'];
        $actual = array_map(function($post){
            return $post->title;
        }, $posts);
        $this->assertEquals($expected, $actual);
    }

    public function testLimit()
    {
        $posts = QueryBuilder::query(Post::class)
            ->limit(2)
            ->getAll();                
        $expected = 2;

        $this->assertCount($expected, $posts);
    }



    public function testUpdate()
    {
        $updateData =[
            'title' => 'test1-1',
            'body' => 'test post 1-1'
        ];
        QueryBuilder::query(Post::class)
            ->where('title', ':title')->bindParam(':title', 'test1')
            ->update($updateData);

        $this->assertTrue($this->seeInDatabase('posts', $updateData));
    }

    public function testDelete()
    {
        $title = 'test1';

        QueryBuilder::query(Post::class)
            ->where('title', $title)
            ->delete();

        $this->assertFalse($this->seeInDatabase('posts', ['title'=>$title]));
    }
}