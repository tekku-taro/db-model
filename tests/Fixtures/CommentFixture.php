<?php
namespace Taro\Tests\Fixtures;


class CommentFixture extends Fixture
{
    public static $default = [
        ['id'=>1, 'title'=>'comment1', 'post_id'=>'1'],
        ['id'=>2, 'title'=>'comment2', 'post_id'=>'3'],
        ['id'=>3, 'title'=>'comment3', 'post_id'=>'1'],
        ['id'=>4,  'title'=>'comment4', 'post_id'=>'2'],
        ['id'=>5,  'title'=>'comment5', 'post_id'=>'3'],
    ];  
}