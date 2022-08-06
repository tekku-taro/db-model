<?php
namespace Taro\DBModel\Schema;

class DBCommandFactory
{
    public static function useDB($config, DbDriver $driver)
    {        
        switch ($driver->type) {
            case DbDriver::MY_SQL:
                return 'USE ' . $config['dbname'] . ';';
            case DbDriver::POSTGRE_SQL:
                return "set schema '" . $config['schema'] . "';";
        }  
    }

    public static function createDatabase($name, $encoding = null, DbDriver $driver)
    {        
        switch ($driver->type) {
            case DbDriver::MY_SQL:
                $encoding = $encoding ?? 'utf8mb4';
                return 'CREATE DATABASE ' . $name . ' CHARACTER SET ' . $encoding;
            case DbDriver::POSTGRE_SQL:
                $encoding = $encoding ?? 'UTF8';
                return 'CREATE DATABASE ' . $name . ' ENCODING ' . $encoding;
        }  
    }
}