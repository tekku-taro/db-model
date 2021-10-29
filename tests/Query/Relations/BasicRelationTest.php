<?php

use PHPUnit\Framework\TestCase;
use Taro\DBModel\DB\DB;
use Taro\DBModel\DB\DbManipulator;
use Taro\DBModel\Models\Post;
use Taro\DBModel\Models\User;
use Taro\DBModel\Query\Relations\BelongsTo;
use Taro\DBModel\Query\Relations\HasMany;
use Taro\DBModel\Query\Relations\HasOne;
use Taro\DBModel\Query\Relations\RelationParams;
use Taro\Tests\Traits\TableSetupTrait;

class BasicRelationTest extends TestCase
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
        $this->fillTable('posts', ['withUserId']);
        $this->fillTable('users');
        $this->dbManipulator = $this->db->getManipulator();
    }

    public function tearDown():void
    {
        $this->clearTable('posts');   
        $this->clearTable('users');   
        $this->db->stop();
    }


    public function testHasMany()
    {
        $params = new RelationParams([
            'fKey' => 'user_id',
            'modelName' => Post::class,
            'fkVal' => 2,
        ]);
        $builder = new HasMany($params, $this->dbManipulator, false);
        $actual = $builder->toSql();
        $expected ='SELECT * FROM posts WHERE user_id = 2 AND user_id IS NOT NULL ;';
        $this->assertEquals($expected, $actual);
        
        $actual = $builder->select('title', 'user_id')->getArrayAll()->toArray();
        
        // var_export($actual);
        $expected = [
            array (
              'title' => 'test2',
              'user_id' => 2,
            ),
            array (
              'title' => 'test3',
              'user_id' => 2,
            ),
        ];
        $this->assertEquals($expected, $actual);
    }

    public function testHasManyInsert()
    {
        $data = [
            'title'=>'test6',
            'views'=>'6',            
        ];
        /** @var User $user */
        $user = User::query()->orderBy('id', 'ASC')->getFirst();
        $user->relatedPosts()->insert($data);
        $data['user_id'] = $user->id;

        $this->assertTrue($this->seeInDatabase('posts', $data));
    }

    public function testHasManyUpdate()
    {
        $data =[
            'title' => 'test1-1',
            'body' => 'test post 1-1'
        ];
        /** @var User $user */
        $user = User::query()->findById(2);     
        
        $user->relatedPosts()
            ->where('title', ':title')->bindParam(':title', 'test2')
            ->update($data);

        $data['user_id'] = $user->id;

        $this->assertTrue($this->seeInDatabase('posts', $data));
    }

    public function testHasManyDelete()
    {
        $title = 'test2';

        /** @var User $user */
        $user = User::query()->findById(2);     
        
        $user->relatedPosts()
            ->where('title', ':title')->bindParam(':title', $title)
            ->delete();

        $this->assertFalse($this->seeInDatabase('posts', ['title'=>$title]));
    }


    public function testHasOne()
    {
        $params = new RelationParams([
            'fKey' => 'user_id',
            'modelName' => Post::class,
            'fkVal' => 2,
        ]);
        $builder = new HasOne($params, $this->dbManipulator, false);
        $actual = $builder->toSql();
        $expected ='SELECT * FROM posts WHERE user_id = 2 AND user_id IS NOT NULL ;';
        $this->assertEquals($expected, $actual);
        
        $actual = $builder->select('title', 'user_id')->getArrayAll()->toArray();
        
        // var_export($actual);
        $expected = [
            array (
              'title' => 'test2',
              'user_id' => 2,
            ),
            array (
              'title' => 'test3',
              'user_id' => 2,
            ),
        ];
        $this->assertEquals($expected, $actual);
    }

    public function testBelongsTo()
    {
        $params = new RelationParams([
            'pKey' => 'id',
            'modelName' => User::class,
            'pkVal' => 2,
        ]);
        $builder = new BelongsTo($params, $this->dbManipulator, false);
        $actual = $builder->toSql();
        $expected ='SELECT * FROM users WHERE id = 2 ;';
        $this->assertEquals($expected, $actual);
        
        $actual = $builder->select('id', 'name')->getArrayAll()->toArray();
        
        // var_export($actual);
        $expected = [
            array (
              'id' => 2,
              'name' => 'user2',
            ),
        ];
        $this->assertEquals($expected, $actual);
    }


}