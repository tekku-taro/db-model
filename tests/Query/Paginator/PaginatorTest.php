<?php

use PHPUnit\Framework\TestCase;
use Taro\DBModel\DB\DB;
use Taro\DBModel\Http\Request;
use Taro\DBModel\Models\Post;
use Taro\DBModel\Query\Pagination\BasicLinks;
use Taro\DBModel\Query\Pagination\BootstrapLinks;
use Taro\DBModel\Query\Pagination\Paginator;
use Taro\DBModel\Query\QueryBuilder;
use Taro\Tests\Traits\TableSetupTrait;

class PaginatorTest extends TestCase
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


    private function createGetRequest(array $get)
    {
        $server = [
            'HTTP_HOST'=>'localhost',
            'REQUEST_URI'=>'/posts',
            'SCRIPT_NAME'=>'/posts?key1=value1',
        ];
        Request::create([
            'get' => $get,
            'server' => $server,
        ]);
    }


    public function testPaginate()
    {
        $get = [Paginator::PAGE_NO_PARAM => 0];
        $this->createGetRequest($get);

        $query = QueryBuilder::query(Post::class);

        $results = $query->select('title','finished')
            ->paginate(2);

        $expected = 'test1';

        $this->assertInstanceOf(Paginator::class, $results);
        $this->assertEquals($expected, $results->first()->title);
    
        $get = [Paginator::PAGE_NO_PARAM => 1];
        $this->createGetRequest($get);

        $query = QueryBuilder::query(Post::class);

        $results = $query->select('title','finished')
            ->paginate(2);

        $expected = 'test3';

        $this->assertCount(2, $results);
        $this->assertEquals($expected, $results->first()->title);
    
        $get = [Paginator::PAGE_NO_PARAM => 2];
        $this->createGetRequest($get);

        $query = QueryBuilder::query(Post::class);

        $results = $query->select('title','finished')
            ->paginate(2);

        $expected = 'test5';

        $this->assertCount(1, $results);
        $this->assertEquals($expected, $results->first()->title);
    
    
    }    

    public function testPaginateArray()
    {
        $get = [Paginator::PAGE_NO_PARAM => 0];
        $this->createGetRequest($get);

        $query = QueryBuilder::query(Post::class);

        $results = $query->select('title','finished')
            ->paginateArray(2);

        $expected = 'test1';

        $this->assertInstanceOf(Paginator::class, $results);
        $this->assertEquals($expected, $results->item(0)['title']);
    
        $get = [Paginator::PAGE_NO_PARAM => 1];
        $this->createGetRequest($get);

        $query = QueryBuilder::query(Post::class);

        $results = $query->select('title','finished')
            ->paginateArray(2);

        $expected = 'test3';

        $this->assertCount(2, $results);
        $this->assertEquals($expected, $results->item(0)['title']);
    
        $get = [Paginator::PAGE_NO_PARAM => 2];
        $this->createGetRequest($get);

        $query = QueryBuilder::query(Post::class);

        $results = $query->select('title','finished')
            ->paginateArray(2);

        $expected = 'test5';

        $this->assertCount(1, $results);
        $this->assertEquals($expected, $results->item(0)['title']);  
    }    

    public function testGetLinkData()
    {
    
        $get = [Paginator::PAGE_NO_PARAM => 2];
        $this->createGetRequest($get);

        $query = QueryBuilder::query(Post::class);

        $results = $query->select()
            ->paginate(2);        
            
        $expected =[ 
            'routeUrl' => 'http://localhost/posts?key1=value1',
            'links' =>
            array (
                array (
                'label' => '最初',
                'href' => 'http://localhost/posts?key1=value1?pageNo=0',
                ),
                array (
                'label' => '前',
                'href' => 'http://localhost/posts?key1=value1?pageNo=1',
                ),
                array (
                'label' => 1,
                'href' => 'http://localhost/posts?key1=value1?pageNo=0',
                ),
                array (
                'label' => 2,
                'href' => 'http://localhost/posts?key1=value1?pageNo=1',
                ),
                array (
                'label' => 3,
                'href' => 'http://localhost/posts?key1=value1?pageNo=2',
                'selected' => true,
                'disabled' => true,
                ),
                array (
                'label' => '次',
                'href' => 'http://localhost/posts?key1=value1?pageNo=2',
                'disabled' => true,
                ),
                array (
                'label' => '最後',
                'href' => 'http://localhost/posts?key1=value1?pageNo=2',
                'disabled' => true,
                ),
            ),       
        ];

        $this->assertEquals($expected, $results->getLinkData());
    }

    public function testDispLinks()
    {
    
        $get = [Paginator::PAGE_NO_PARAM => 0];
        $this->createGetRequest($get);

        $query = QueryBuilder::query(Post::class);

        $results = $query->select()
            ->paginate(2);        
        
        // var_export($results->setTemplate(new BasicLinks)->dispLinks());

        $expected =<<< END
<div class="pagination"><<|<|<a href="http://localhost/posts?key1=value1?pageNo=0" class="pagination-link">1</a>|<a href="http://localhost/posts?key1=value1?pageNo=1" class="pagination-link">2</a>|<a href="http://localhost/posts?key1=value1?pageNo=2" class="pagination-link">3</a>|<a href="http://localhost/posts?key1=value1?pageNo=1" class="pagination-link">></a>|<a href="http://localhost/posts?key1=value1?pageNo=2" class="pagination-link">>></a></div>
END;

        $this->assertEquals($expected, $results->setTemplate(new BasicLinks)->dispLinks());
        
        var_export($results->setTemplate(new BootstrapLinks)->dispLinks());

        $expected =<<< END
<nav aria-label="Page navigation"><ul class="pagination"><li class="page-item disabled"><a href="http://localhost/posts?key1=value1?pageNo=0" class="page-link" tabindex="-1">最初</a></li><li class="page-item disabled"><a href="http://localhost/posts?key1=value1?pageNo=0" class="page-link" tabindex="-1">前</a></li><li class="page-item active"><a href="http://localhost/posts?key1=value1?pageNo=0" class="page-link">1</a></li><li class="page-item"><a href="http://localhost/posts?key1=value1?pageNo=1" class="page-link">2</a></li><li class="page-item"><a href="http://localhost/posts?key1=value1?pageNo=2" class="page-link">3</a></li><li class="page-item"><a href="http://localhost/posts?key1=value1?pageNo=1" class="page-link">次</a></li><li class="page-item"><a href="http://localhost/posts?key1=value1?pageNo=2" class="page-link">最後</a></li></ul></nav>
END;

        $this->assertEquals($expected, $results->setTemplate(new BootstrapLinks)->dispLinks());
    }
}