<?php
namespace Taro\Tests\Fixtures;


class UserFixture extends Fixture
{
    public static $default = [
        ['id'=>1, 'name'=>'user1', 'password'=>'123'],
        ['id'=>2, 'name'=>'user2', 'password'=>'123'],
        ['id'=>3, 'name'=>'user3', 'password'=>'dd'],
        ['id'=>4, 'name'=>'user4', 'password'=>'abc'],
    ];   
}