<?php

use PHPUnit\Framework\TestCase;
use Taro\DBModel\DB\DB;
use Taro\DBModel\Models\Post;
use Taro\DBModel\Query\QueryBuilder;
use Taro\DBModel\Utilities\DataManager\ObjectList;
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
        $this->clearTable('posts');
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

        $this->assertInstanceOf(ObjectList::class, $results);
        $this->assertInstanceOf(Post::class, $results->first());
        $this->assertEquals('test1', $results->first()->title);
    }

    public function testCount()
    {
        $query = QueryBuilder::query(Post::class);
        $results = $query->count();

        $this->assertEquals(5, $results);

        $query = QueryBuilder::query(Post::class);
        $results = $query->groupBy('hidden')->count('hidden');

        $expected = [
            ['hidden'=>'public','hidden_count'=>3],
            ['hidden'=>'secret','hidden_count'=>2],
        ];
        $this->assertEquals($expected, $results->toArray());

        $query = QueryBuilder::query(Post::class);
        $results = $query->groupBy('hidden', 'finished')->count('hidden');

        $expected = [
            ['hidden'=>'public','finished'=>0,'hidden_count'=>1],
            ['hidden'=>'public','finished'=>1,'hidden_count'=>2],
            ['hidden'=>'secret','finished'=>0,'hidden_count'=>1],
            ['hidden'=>'secret','finished'=>1,'hidden_count'=>1],
        ];
        $this->assertEquals($expected, $results->toArray());
    }

    public function testAverage()
    {
        $query = QueryBuilder::query(Post::class);
        $results = $query->average('views');

        $this->assertEquals(3, $results);
    }

    public function testSum()
    {
        $query = QueryBuilder::query(Post::class);
        $results = $query->sum('views');

        $this->assertEquals(15, $results);
    }

    public function testMax()
    {
        $query = QueryBuilder::query(Post::class);
        $results = $query->max('views');

        $this->assertEquals(5, $results);
    }

    public function testMin()
    {
        $query = QueryBuilder::query(Post::class);
        $results = $query->min('views');

        $this->assertEquals(1, $results);
    }

    public function testWhere()
    {
        $posts = QueryBuilder::query(Post::class)
            ->where('title', 'test1')
            ->getAll();

        $expected = 'test1';

        $this->assertEquals($expected, $posts->first()->title);

        $posts = QueryBuilder::query(Post::class)
            ->where('title', 'IN', ['test1', 'test2'])
            ->getAll();

        $expected = ['test1', 'test2'];
        $this->assertCount(2, $posts);
        $this->assertEquals($expected, [$posts->first()->title,$posts->item(1)->title]);

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

        $this->assertEquals($expected, $posts->first()->title);
    }

    public function testOrderBy()
    {
        $posts = QueryBuilder::query(Post::class)
            ->orderBy('views', 'DESC')
            ->getAll();        

        $expected = ['test1','test2','test5','test4','test3'];
        $actual = $posts->map(function($post){
            return $post->title;
        })->toArray();
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