<?php

use PhpParser\Comment;
use PHPUnit\Framework\TestCase;
use Taro\DBModel\DB\DB;
use Taro\DBModel\DB\DbManipulator;
use Taro\DBModel\Models\User;
use Taro\DBModel\Query\Relations\HasManyThrough;
use Taro\DBModel\Query\Relations\RelationBuilder;
use Taro\DBModel\Query\Relations\RelationParams;
use Taro\Tests\Traits\TableSetupTrait;

class HasManyThroughRelationTest extends TestCase
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
        $this->fillTable('posts', ['withUserId']);
        $this->fillTable('users');
        $this->fillTable('comments');
        $this->dbManipulator = $this->db->getManipulator();
    }

    public function tearDown():void
    {
        $this->clearTable('posts');   
        $this->clearTable('users');   
        $this->clearTable('comments');   
        $this->db->stop();
    }

    private function buildHasManyThrough($localVal):HasManyThrough
    {
        $params = new RelationParams([
            'fKey' => 'post_id',
            'middleFKey' => 'user_id',
            'middleLKey' => 'id',
            'middleTable' => 'posts',
            'modelName' => Comment::class,
            'relkVal' => $localVal
        ]);
        return new HasManyThrough($params, $this->dbManipulator, false);
    }

    public function testHasManyThrough()
    {
        $builder = $this->buildHasManyThrough(1);
        $actual = $builder->toSql();
        $expected ='SELECT comments.*,posts.user_id AS '. RelationBuilder::MAP_KEY . //'
         '  FROM comments  INNER JOIN posts ON ( comments.post_id = posts.id ) WHERE posts.user_id = 1 ;';
        $this->assertEquals($expected, $actual); //'
        
        $actual = $builder->select('title', 'posts.user_id')->getArrayAll()->toArray();
        
        var_export($actual);
        $expected = [
            array (
              'title' => 'comment1',
              'user_id' => 1,       
              RelationBuilder::MAP_KEY => 1
            ),
            array (
              'title' => 'comment3',
              'user_id' => 1,
              RelationBuilder::MAP_KEY => 1
            ),                     
        ];
        $this->assertEquals($expected, $actual);
    }

    public function testBuilderHasManyThrough()
    {
        /** @var User $user */
        $user = User::query()->findById(1);
        $actual = $user->userComments()->select('id','title')->getArrayAll()->toArray();
        
        var_export($actual);
        $expected = [
            array (
              'id' => 1,
              'title' => 'comment1',
              RelationBuilder::MAP_KEY => 1
            ),
            array (
              'id' => 3,
              'title' => 'comment3',
              RelationBuilder::MAP_KEY => 1
            ),                     
        ];
        $this->assertEquals($expected, $actual);        
    }

}