<?php

use PHPUnit\Framework\TestCase;
use Taro\DBModel\DB\DB;
use Taro\DBModel\Query\DirectSql;
use Taro\DBModel\Models\Post;
use Taro\DBModel\Query\Clauses\Wh;
use Taro\Tests\Fixtures\PostFixture;
use Taro\Tests\Traits\TableSetupTrait;

class DirectSqlTest extends TestCase
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
        DirectSql::query()->table('posts')->insert(PostFixture::$default[0]);

        $this->assertTrue($this->seeInDatabase('posts', PostFixture::$default[0]));
    }

    public function testBulkInsert()
    {
        $this->clearTable('posts');
        DirectSql::query()->table('posts')->bulkInsert(PostFixture::$default);
        
        $failures = array_filter(PostFixture::$default, function($record) {
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

        $expected = PostFixture::$default[0];
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

    public function testWhereWithWh()
    {
        $query = DirectSql::query()->table('posts')->select('title');
        $where = new Wh();       
        $where->addAnd('views', '>', '2');
        $where->addAnd('hidden','public');
        $where->addOr('title', 'test3');
        $query->addWhClause($where);

        $posts = $query->getAsArray();
        // var_export($posts);
        $expected = [
            array (  
              'title' => 'test3',
            ),
            array (
              'title' => 'test5',
            ),
        ];

        $this->assertEquals($expected, $posts);

        $query2 = DirectSql::query()->table('posts')->select('title');
        $where = new Wh();
        $where->addBlock(
            Wh::and(
                Wh::block('hidden', 'public'),
                Wh::or(
                    Wh::block('views', '>', '2'),
                    Wh::block('title', 'test3')
                )
            )
        );    
        $query2->addWhClause($where);

        $posts = $query2->getAsArray();
        // var_export($posts);

        $this->assertEquals($expected, $posts);
    } 


    public function testBindParam()
    {
        $posts = DirectSql::query()->table('posts')
            ->where('title', ':title1')->bindParam(':title1', 'test1')
            ->getAsModels(Post::class);

        $expected = 'test1';

        $this->assertEquals($expected, $posts[0]->title);
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