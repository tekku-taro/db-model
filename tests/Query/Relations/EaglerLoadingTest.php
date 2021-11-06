<?php

use PHPUnit\Framework\TestCase;
use Taro\DBModel\DB\DB;
use Taro\DBModel\DB\DbManipulator;
use Taro\DBModel\Models\Post;
use Taro\DBModel\Models\User;
use Taro\DBModel\Utilities\DataManager\ObjectList;
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

    private function fetchPostMap(array $userIdList, $relation):array
    {
        $postMap = [];
        foreach ($userIdList as $userId) {
            /** @var User $user */
            $user = User::query()->findById($userId);
            $postMap[$userId] = $user->{$relation}()->getAll();
        }
        return $postMap;
    }

    private function fetchCommentMap(array $postIdList, $relation):array
    {
        $commentMap = [];
        foreach ($postIdList as $postId) {
            /** @var Post $post */
            $post = Post::query()->findById($postId);
            $commentMap[$postId] = $post->{$relation}()->getAll();
        }
        return $commentMap;
    }

    private function fetchPostMapWithComments(array $userIdList, $relation, $subRelation)
    {
        $postMap = $this->fetchPostMap($userIdList, $relation);

        foreach ($postMap as $postList) {
            /** @var ObjectList $postList */
            $postIdList = $postList->pluck('id');
            $commentMap = $this->fetchCommentMap($postIdList, $subRelation);
            /** @var Post $post */
            foreach ($postList as $post) {
                $post->setDynamicProperty($subRelation, $commentMap[$post->id]);
            }
            
        }
        return $postMap;
    }

    public function testHasMany()
    {
        $actual = User::query()->where('password', '123')->select('id','name')
            ->eagerLoad(['relatedPosts'])    
            ->getAll();
        
        $expected = User::query()->where('password', '123')->select('id','name')   
            ->getAll();
        $postMap = $this->fetchPostMap([1,2,3], 'relatedPosts');
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
               
        $expected = User::query()->where('password', '123')->select('id','name')   
            ->getAll();
        $postMap = $this->fetchPostMap([1,2], 'favoritePosts');
        foreach ($expected as $user) {
            $user->setDynamicProperty('favoritePosts', $postMap[$user->id]);
        }
        $this->assertEquals($expected, $actual);
    }

    public function testHasManyThrough()
    {  
        $actual = User::query()->where('password', '123')->select('id','name')
            ->eagerLoad(['userComments'])    
            ->getAll();
               
        $expected = User::query()->where('password', '123')->select('id','name')
        ->getAll();

        $postMap = $this->fetchPostMap([1,2], 'userComments');

        foreach ($expected as $user) {
            $user->setDynamicProperty('userComments', $postMap[$user->id]);
        }
        $this->assertEquals($expected, $actual);
    }

    public function testDeepEagerLoad()
    {  
        $actual = User::query()->where('password', '123')->select('id','name')
            ->eagerLoad(['relatedComments', 'relatedPosts'])    
            ->getAll();
               
        $expected = User::query()->where('password', '123')->select('id','name')
        ->getAll();

        $postMap = $this->fetchPostMapWithComments([1,2], 'relatedPosts', 'relatedComments');

        foreach ($expected as $user) {
            $user->setDynamicProperty('relatedPosts', $postMap[$user->id]);
        }
        $this->assertEquals($expected, $actual);
    }

}