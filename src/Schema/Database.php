<?php
namespace Taro\DBModel\Schema;

use Taro\DBModel\DB\DB;

class Database
{
    
    public static function create($name, $encoding = null, $configConnName = null)
    {
        $db = self::getDb($name, $configConnName);
        // $sql = 'CREATE DATABASE ' . $name . ' CHARACTER SET ' . $encoding;
        $sql = DBCommandFactory::createDatabase($name, $encoding, DB::getDriver($name));

        $result = $db->getManipulator()->exec($sql);
        $db->stop();
        return $result;
    }

    public static function dropIfExists($name, $configConnName = null)
    {
        $sql = 'DROP DATABASE IF EXISTS ' . $name;
        $db = self::getDb($name, $configConnName);
        $result = $db->getManipulator()->exec($sql);
        $db->stop();
        return $result;
    }


    private static function getDb($name, $configConnName = null):DB
    {
        $db = DB::start($configConnName, false, true, $name);
        return $db;        
    }

}