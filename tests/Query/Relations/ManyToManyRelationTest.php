<?php

use PHPUnit\Framework\TestCase;
use Taro\DBModel\DB\DB;
use Taro\DBModel\DB\DbManipulator;
use Taro\DBModel\Models\Post;
use Taro\DBModel\Models\User;
use Taro\DBModel\Query\Relations\ManyToMany;
use Taro\DBModel\Query\Relations\RelationParams;
use Taro\Tests\Traits\TableSetupTrait;

class ManyToManyRelationTest extends TestCase
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
        $this->clearTable('favorites');         
        $this->fillTable('posts', ['withUserId']);
        $this->fillTable('users');
        $this->fillTable('favorites');
        $this->dbManipulator = $this->db->getManipulator();
    }

    public function tearDown():void
    {
        $this->clearTable('posts');   
        $this->clearTable('users');   
        $this->db->stop();
    }

    private function buildManyToMany($relatedModel, $relkVal):ManyToMany
    {
        $params = new RelationParams([
            'pKey' => 'id',
            'fKey' => 'post_id',
            'relKey' => 'user_id',
            'pivotTable' => 'favorites',
            'modelName' => $relatedModel,
            'relkVal' => $relkVal
        ]);
        return new ManyToMany($params, $this->dbManipulator, false);
    }

    public function testManyToMany()
    {
        $builder = $this->buildManyToMany(Post::class, 1);
        $actual = $builder->toSql();
        $expected ='SELECT * FROM posts  INNER JOIN favorites ON ( posts.id = favorites.post_id ) WHERE favorites.user_id = 1 ;';
        $this->assertEquals($expected, $actual);
        
        $actual = $builder->select('title', 'favorites.user_id')->getArrayAll();
        
        var_export($actual);
        $expected = [
            array (
              'title' => 'test2',
              'user_id' => 1,
            ),
            array (
              'title' => 'test3',
              'user_id' => 1,
            ),            
        ];
        $this->assertEquals($expected, $actual);
    }

    public function testBuilderManyToMany()
    {
        /** @var User $user */
        $user = User::query()->findById(1);
        $actual = $user->favoritePosts()->select('id','title')->getArrayAll();
        
        var_export($actual);
        $expected = [   
            array (
                'id' => 2,
                'title' => 'test2',
            ),
            array (
                'id' => 3,
                'title' => 'test3',
            ),          
        ];
        $this->assertEquals($expected, $actual);        
    }

    public function testInsertPivot()
    {
        $record = [
            'user_id' => 3,
            'post_id' => 5,
            'star' => 10
        ];

        /** @var User $user */
        $user = User::query()->findById($record['user_id']);
        $actual = $user->favoritePosts()->insertPivot($record['post_id'], ['star'=>$record['star']]);
        
        $this->assertTrue($actual);        
        $this->assertTrue($this->seeInDatabase('favorites', $record));        
    }

    public function testUpdatePivot()
    {
        $updateRecord = [
            'user_id' => 1,
            'post_id' => 3,
            'star' => 5
        ];

        /** @var User $user */
        $user = User::query()->findById($updateRecord['user_id']);
        $actual = $user->favoritePosts()->updatePivot($updateRecord['post_id'], ['star'=>$updateRecord['star']]);
        
        $this->assertTrue($actual);        
        $this->assertTrue($this->seeInDatabase('favorites', $updateRecord));        
    }

    public function testUpdatePivotUnderCondition()
    {
        $otherRecord = [
            'user_id' => 1,
            'post_id' => 3,
            'star' => 2
        ];

        $updateRecord = [
            'user_id' => 1,
            'post_id' => 3,
            'star' => 5
        ];

        $conditions = [
            ['star', '<', 2]
        ];

        /** @var User $user */
        $user = User::query()->findById($updateRecord['user_id']);
        // 更新しないレコードを用意
        $user->favoritePosts()->insertPivot($otherRecord['post_id'], ['star'=>$otherRecord['star']]);

        $user->favoritePosts()->updatePivot($updateRecord['post_id'], ['star'=>$updateRecord['star']], $conditions);
  
        $this->assertTrue($this->seeInDatabase('favorites', $updateRecord));        
    }

    public function testDeletePivot()
    {
        $record = [            
            'user_id' => 1,
            'post_id' => 3,
        ];

        /** @var User $user */
        $user = User::query()->findById($record['user_id']);
        $actual = $user->favoritePosts()->deletePivot($record['post_id']);
        
        $this->assertTrue($actual);        
        $this->assertFalse($this->seeInDatabase('favorites', $record));        
    }

}