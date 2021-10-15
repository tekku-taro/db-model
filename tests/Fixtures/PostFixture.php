<?php
namespace Taro\Tests\Fixtures;


class PostFixture extends Fixture
{
    public static $default = [
        ['id'=>1, 'title'=>'test1', 'views'=>'5', 'finished'=>0, 'hidden'=>'secret' ],
        ['id'=>2, 'title'=>'test2', 'views'=>'4', 'finished'=>1, 'hidden'=>'secret' ],
        ['id'=>3, 'title'=>'test3', 'views'=>'1', 'finished'=>1, 'hidden'=>'public' ],
        ['id'=>4,  'title'=>'test4', 'views'=>'2', 'finished'=>1, 'hidden'=>'public' ],
        ['id'=>5,  'title'=>'test5', 'views'=>'3', 'finished'=>0, 'hidden'=>'public' ],
    ];    
    public static $withUserId = [
        ['id'=>1, 'title'=>'test1', 'user_id'=>1, 'views'=>'5', 'finished'=>0, 'hidden'=>'secret' ],
        ['id'=>2, 'title'=>'test2', 'user_id'=>2, 'views'=>'4', 'finished'=>1, 'hidden'=>'secret' ],
        ['id'=>3, 'title'=>'test3', 'user_id'=>2, 'views'=>'1', 'finished'=>1, 'hidden'=>'public' ],
        ['id'=>4,  'title'=>'test4','user_id'=>4,  'views'=>'2', 'finished'=>1, 'hidden'=>'public' ],
        ['id'=>5,  'title'=>'test5','user_id'=>3,  'views'=>'3', 'finished'=>0, 'hidden'=>'public' ],
    ];  
}