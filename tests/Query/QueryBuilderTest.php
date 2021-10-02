<?php

use PHPUnit\Framework\TestCase;
use Taro\DBModel\DB\DB;
use Taro\DBModel\Models\Post;
use Taro\DBModel\Query\QueryBuilder;
use Taro\Tests\Fixtures\PostFixture;
use Taro\Tests\Traits\TableSetupTrait;

class QueryBuilderTest extends TestCase
{
    use TableSetupTrait;

    /** @var DB $db */
    private $db;

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



    public function testInsert()
    {
        $this->clearTable('posts');
        QueryBuilder::query(Post::class)->insert(PostFixture::$default[0]);

        $this->assertTrue($this->seeInDatabase('posts', PostFixture::$default[0]));
    }

    public function testBulkInsert()
    {
        $this->clearTable('posts');
        QueryBuilder::query(Post::class)->bulkInsert(PostFixture::$default);
        
        $failures = array_filter(PostFixture::$default, function($record) {
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