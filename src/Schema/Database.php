<?php
namespace Taro\DBModel\Schema;

use Taro\DBModel\DB\DB;

class Database
{
    
    public static function create($name, $encoding = null, $configConnName = null)
    {
        $dbManipulator = self::getDbManipulator($name, $configConnName);
        // $sql = 'CREATE DATABASE ' . $name . ' CHARACTER SET ' . $encoding;
        $sql = DBCommandFactory::createDatabase($name, $encoding, DB::getDriver($name));

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
        $db = DB::start($configConnName, false, true, $name);
        // ['config'=>$config] = DB::loadConfig($configConnName);
        // $config['dbname'] = null;
        // $dbh = DbConnection::open($name, $config);
        return $db->getManipulator();        
    }

}