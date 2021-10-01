<?php

use PHPUnit\Framework\TestCase;
use Taro\DBModel\DB\DB;
use Taro\DBModel\DB\DbManipulator;
use Taro\DBModel\Models\Post;
use Taro\DBModel\Models\User;
use Taro\DBModel\Query\DirectSql;
use Taro\DBModel\Query\Relations\BelongsTo;
use Taro\DBModel\Query\Relations\HasMany;
use Taro\DBModel\Query\Relations\HasOne;
use Taro\DBModel\Query\Relations\RelationParams;

class BasicRelationTest extends TestCase
{
    private $connName = 'mysql';

    /** @var DB $db */
    private $db;
    /** @var DbManipulator $dbManipulator */    
    private $dbManipulator;

    private $posts = [
        ['title'=>'test1', 'user_id'=>1, 'views'=>'5', 'finished'=>0, 'hidden'=>'secret' ],
        ['title'=>'test2', 'user_id'=>2, 'views'=>'4', 'finished'=>1, 'hidden'=>'secret' ],
        ['title'=>'test3', 'user_id'=>2, 'views'=>'1', 'finished'=>1, 'hidden'=>'public' ],
        [ 'title'=>'test4','user_id'=>4,  'views'=>'2', 'finished'=>1, 'hidden'=>'public' ],
        [ 'title'=>'test5','user_id'=>3,  'views'=>'3', 'finished'=>0, 'hidden'=>'public' ],
    ];

    private $users = [
        ['id'=>1, 'name'=>'user1', 'password'=>'123'],
        ['id'=>2, 'name'=>'user2', 'password'=>'123'],
        ['id'=>3, 'name'=>'user3', 'password'=>'dd'],
        ['id'=>4, 'name'=>'user4', 'password'=>'abc'],
    ];

    public function setUp():void
    {
        $this->setupConnection();
        $this->clearTable('posts');   
        $this->clearTable('users');         
        $this->fillTable('posts', $this->posts);
        $this->fillTable('users', $this->users);
        $this->dbManipulator = $this->db->getManipulator();
    }

    public function tearDown():void
    {
        $this->clearTable('posts');   
        $this->clearTable('users');   
        $this->db->stop();
    }

    private function clearTable($tableName)
    {
        $sql = 'DELETE FROM ' . $tableName . ' WHERE 1 = 1;';
        $dbh = $this->db->getPdo();
        $stmt = $dbh->query($sql);

    }

    private function setupConnection()
    {
        $this->db = DB::start($this->connName, true);
    }

    private function seeInDatabase($table, $data)
    {
        $sql = 'SELECT count(*) FROM ' . $table . ' WHERE ';
        foreach ($data as $key => $value) {
            $whereClause[] = $key . ' = "' . $value . '"';
        }

        $sql .= implode(' AND ', $whereClause);

        $dbh = $this->db->getPdo();

        $stmt = $dbh->query($sql);
        if ($stmt->fetchColumn() > 0) {
            return true;
        }
        return false;
    }

    private function fillTable($tableName, $data)
    {
        DirectSql::query()->table($tableName)->bulkInsert($data);
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
        
        $actual = $builder->select('title', 'user_id')->getArrayAll();
        
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
        
        $actual = $builder->select('title', 'user_id')->getArrayAll();
        
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
        
        $actual = $builder->select('id', 'name')->getArrayAll();
        
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