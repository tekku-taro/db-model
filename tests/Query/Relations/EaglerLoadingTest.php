<?php

use PhpParser\Comment;
use PHPUnit\Framework\TestCase;
use Taro\DBModel\DB\DB;
use Taro\DBModel\DB\DbManipulator;
use Taro\DBModel\Models\Post;
use Taro\DBModel\Models\User;
use Taro\DBModel\Query\Relations\HasManyThrough;
use Taro\DBModel\Query\Relations\RelationParams;
use Taro\Tests\Traits\TableSetupTrait;

class EaglerLoadingTest extends TestCase
{
    use TableSetupTrait;

    /** @var DB $db */
    private $db;
    /** @var DbManipulator $dbManipulator */    
    private $dbManipulator;

    public function setUp():void
    {
        $this->setupConnection();
        $this->clearTable('posts');   
        $this->clearTable('users');         
        $this->clearTable('comments');         
        $this->clearTable('favorites');         
        $this->fillTable('posts', ['withUserId']);
        $this->fillTable('users');
        $this->fillTable('comments');
        $this->fillTable('favorites');
        $this->dbManipulator = $this->db->getManipulator();
    }

    public function tearDown():void
    {
        $this->clearTable('posts');   
        $this->clearTable('users');   
        $this->clearTable('comments');
        $this->clearTable('favorites');   
        $this->db->stop();
    }

    public function testHasMany()
    {
        $actual = User::query()->where('password', '123')->select('id','name')
            ->eagerLoad(['relatedPosts'])    
            ->getAll();
        
        // var_export($actual);
        $expected = User::query()->where('password', '123')->select('id','name')   
            ->getAll();
        $postMap = Post::query()->where('id', 'IN', [1,2,3])->getAll()->groupBy('user_id');
        foreach ($expected as $user) {
            $user->setDynamicProperty('relatedPosts', $postMap[$user->id]);
        }
        $this->assertEquals($expected, $actual);
    }

    public function testManyToMany()
    {
        $actual = User::query()->where('password', '123')->select('id','name')
            ->eagerLoad(['favoritePosts'])    
            ->getAll();

        /** @var User $user1 */
        $user1 = User::query()->findById(1);
        /** @var User $user2 */
        $user2 = User::query()->findById(2);
        $postMap[1] = $user1->favoritePosts()->getAll();
        $postMap[2] = $user2->favoritePosts()->getAll();
               
        $expected = User::query()->where('password', '123')->select('id','name')   
            ->getAll();

        foreach ($expected as $user) {
            $user->setDynamicProperty('favoritePosts', $postMap[$user->id]);
        }
        $this->assertEquals($expected, $actual);
    }

}