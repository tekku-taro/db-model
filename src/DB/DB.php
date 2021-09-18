<?php
namespace Taro\DBModel\DB;

class DB
{
    private static $dbhList;

    private static $primaryDbh;

    private $dbh;

    public $dbName;

    public $config;


    public function begin()
    {
        
    }

    public function commit()
    {

    }

    public function rollback()
    {

    }

    public function start()
    {

    }

    public function restart()
    {

    }

    public function end()
    {

    }

    public function getManipulator()
    {

    }

    public function getPdo()
    {

    }

    public static function database($dbName): self
    {

    }

}