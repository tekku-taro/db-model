<?php

use PHPUnit\Framework\TestCase;
use Taro\DBModel\DB\DB;
use Taro\DBModel\Models\Post;
use Taro\DBModel\Query\DirectSql;
use Taro\DBModel\Query\JoinFactory;
use Taro\DBModel\Query\QueryBuilder;
use Taro\Tests\Traits\TableSetupTrait;

class JoinTest extends TestCase
{
    use TableSetupTrait;

    /** @var DB $db */
    private $db;

    public function setUp():void
    {
        $this->setupConnection();
        $this->clearTable('posts');   
        $this->clearTable('users');         
        $this->fillTable('posts', ['withUserId']);
        $this->fillTable('users');
    }

    public function tearDown():void
    {
        $this->clearTable('posts');   
        $this->clearTable('users');   
        $this->db->stop();
    }


    public function testJoin()
    {
        $joinBuilder = JoinFactory::create(JoinFactory::JOIN, 'posts');
        $joinBuilder->joinTable('users')->on('user_id', 'id');
        $actual = $joinBuilder->toSql();
        $expected =' INNER JOIN users ON ( posts.user_id = users.id ) ';
        $this->assertEquals($expected, $actual);
    }

    public function testLeftJoin()
    {
        $joinBuilder = JoinFactory::create(JoinFactory::LEFT_JOIN, 'posts');
        $joinBuilder->joinTable('users')->on('user_id', 'id');
        $actual = $joinBuilder->toSql();
        $expected =' LEFT JOIN users ON ( posts.user_id = users.id ) ';
        $this->assertEquals($expected, $actual);
    }

    public function testRightJoin()
    {
        $joinBuilder = JoinFactory::create(JoinFactory::RIGHT_JOIN, 'posts');
        $joinBuilder->joinTable('users')->on('user_id', 'id');
        $actual = $joinBuilder->toSql();
        $expected =' RIGHT JOIN users ON ( posts.user_id = users.id ) ';
        $this->assertEquals($expected, $actual);
    }

    public function testOuterJoin()
    {
        $joinBuilder = JoinFactory::create(JoinFactory::OUTER_JOIN, 'posts');
        $joinBuilder->joinTable('users')->on('user_id', 'id');
        $actual = $joinBuilder->toSql();
        $expected =' OUTER JOIN users ON ( posts.user_id = users.id ) ';
        $this->assertEquals($expected, $actual);
    }


    public function testBuilderJoin()
    {        
        $query = QueryBuilder::query(Post::class)
            ->join('users')->on('user_id', 'id');
        $actual = $query->select('title','users.name')
            ->where('name', 'user1')
            ->getArrayAll();
        // var_export($actual);
        $expected = [
            array (
                'title' => 'test1',
                'name' => 'user1',
              ),
        ];  
        $this->assertEquals($expected, $actual);        
    }

    public function testDirectSqlJoin()
    {        
        $query = DirectSql::query()->table('posts')
            ->join('users')->on('user_id', 'id');
        $actual = $query->select('title','users.name')
            ->where('users.name', 'user2')
            ->getAsArray();
        // var_export($actual);
        $expected = [
            array (
              'title' => 'test2',
              'name' => 'user2',
            ),
            array (
              'title' => 'test3',
              'name' => 'user2',
            ),
        ];  
        $this->assertEquals($expected, $actual);        
    }

}