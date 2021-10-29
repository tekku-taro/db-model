<?php

use PHPUnit\Framework\TestCase;
use Taro\DBModel\DB\DB;
use Taro\DBModel\DB\DbManipulator;
use Taro\DBModel\Models\Comment;
use Taro\DBModel\Models\User;
use Taro\DBModel\Query\Relations\BelongsToThrough;
use Taro\DBModel\Query\Relations\RelationParams;
use Taro\Tests\Traits\TableSetupTrait;

class BelongsToThroughRelationTest extends TestCase
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

    private function buildBelongsToThrough($fkVal):BelongsToThrough
    {
        $params = new RelationParams([
            'pKey' => 'id',
            'middleFKey' => 'user_id',
            'middleLKey' => 'id',
            'middleTable' => 'posts',
            'modelName' => User::class,
            'relkVal' => $fkVal
        ]);
        return new BelongsToThrough($params, $this->dbManipulator, false);
    }

    public function testBelongsToThrough()
    {
        $builder = $this->buildBelongsToThrough(1);
        $actual = $builder->toSql();
        $expected ='SELECT * FROM users  INNER JOIN posts ON ( users.id = posts.user_id ) WHERE posts.id = 1 ;';
        $this->assertEquals($expected, $actual);
        
        $actual = $builder->select('name', 'posts.user_id')->getArrayAll()->toArray();
        
        // var_export($actual);
        $expected = [  
            array (
                'name' => 'user1',
                'user_id' => 1,   
              ),                              
        ];
        $this->assertEquals($expected, $actual);
    }

    public function testBuilderBelongsToThrough()
    {
        /** @var Comment $comment */
        $comment = Comment::query()->findById(1);
        $actual = $comment->users()->select('id','name')->getArrayAll()->toArray();
        
        // var_export($actual);
        $expected = [ 
            array (
                'id' => 1,
                'name' => 'user1',
              ),                             
        ];
        $this->assertEquals($expected, $actual);        
    }

}