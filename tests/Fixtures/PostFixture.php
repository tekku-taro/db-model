<?php
namespace Taro\Tests\Fixtures;


class PostFixture extends Fixture
{
    public static $default = [
        ['title'=>'test1', 'views'=>'5', 'finished'=>0, 'hidden'=>'secret' ],
        ['title'=>'test2', 'views'=>'4', 'finished'=>1, 'hidden'=>'secret' ],
        ['title'=>'test3', 'views'=>'1', 'finished'=>1, 'hidden'=>'public' ],
        [ 'title'=>'test4', 'views'=>'2', 'finished'=>1, 'hidden'=>'public' ],
        [ 'title'=>'test5', 'views'=>'3', 'finished'=>0, 'hidden'=>'public' ],
    ];    
    public static $withUserId = [
        ['title'=>'test1', 'user_id'=>1, 'views'=>'5', 'finished'=>0, 'hidden'=>'secret' ],
        ['title'=>'test2', 'user_id'=>2, 'views'=>'4', 'finished'=>1, 'hidden'=>'secret' ],
        ['title'=>'test3', 'user_id'=>2, 'views'=>'1', 'finished'=>1, 'hidden'=>'public' ],
        [ 'title'=>'test4','user_id'=>4,  'views'=>'2', 'finished'=>1, 'hidden'=>'public' ],
        [ 'title'=>'test5','user_id'=>3,  'views'=>'3', 'finished'=>0, 'hidden'=>'public' ],
    ];  
}