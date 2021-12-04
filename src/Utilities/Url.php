<?php
namespace Taro\DBModel\Utilities;

class Url
{
    public static function getProtocol():string
    {
        return empty($_SERVER['HTTPS']) ? 'http://' : 'https://';
    }

    public static function getServerUrl():string
    {
        return self::getProtocol() . $_SERVER['HTTP_HOST'];
    }  

    public static function getAppUrl():string
    {
        return env('APP_URL');
    } 

    public static function getPageUrl():string
    {
        return self::getProtocol() . request()->server('HTTP_HOST') . request()->server('REQUEST_URI');
    }  

    public static function getPageUrlWithoutQueryStrings():string
    {
        return self::getProtocol() . request()->server('HTTP_HOST') . request()->server('SCRIPT_NAME');
    }  
}