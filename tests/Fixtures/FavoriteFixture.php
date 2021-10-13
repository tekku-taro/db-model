<?php
namespace Taro\Tests\Fixtures;


class FavoriteFixture extends Fixture
{
    public static $default = [
        ['id'=>1, 'user_id'=>2, 'post_id'=>1, 'star'=>3],
        ['id'=>2, 'user_id'=>1, 'post_id'=>2, 'star'=>5],
        ['id'=>3, 'user_id'=>1, 'post_id'=>3, 'star'=>1],
    ];   
}