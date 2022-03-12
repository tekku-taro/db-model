<?php
namespace Taro\DBModel\Schema;

use Taro\DBModel\DB\DB;
use Taro\DBModel\DB\DbConnection;
use Taro\DBModel\DB\DbManipulator;

class Database
{
    
    public static function create($name, $encoding = 'utf8mb4', $configConnName = null)
    {
        $sql = 'CREATE DATABASE ' . $name . ' CHARACTER SET ' . $encoding;

        $dbManipulator = self::getDbManipulator($name, $configConnName);
        return $dbManipulator->exec($sql);
    }

    public static function dropIfExists($name, $configConnName = null)
    {
        $sql = 'DROP DATABASE IF EXISTS ' . $name;
        $dbManipulator = self::getDbManipulator($name, $configConnName);
        return $dbManipulator->exec($sql);
    }


    private static function getDbManipulator($name, $configConnName = null)
    {
        ['config'=>$config] = DB::loadConfig($configConnName);
        $config['dbname'] = null;
        $dbh = DbConnection::open($name, $config);
        return new DbManipulator($dbh);        
    }

}