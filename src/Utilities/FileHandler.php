<?php
namespace Taro\DBModel\Utilities;

define('DS', DIRECTORY_SEPARATOR);

use Taro\DBModel\Exceptions\FileNotFoundException;
use Dotenv\Dotenv;


class FileHandler
{
    public static $envLoaded = false;

    public const CONFIG_PATH = __DIR__ . DS . '..' . DS .'Config' . DS .'database.php';

    public static function saveAs($filePath, $data): bool
    {
        return file_put_contents($filePath, $data);
    }

    public static function load($filePath): mixed
    {
        if(!file_exists($filePath)) {
            throw new FileNotFoundException($filePath . ' のファイルは存在しません。');
        }

        return file_get_contents($filePath);
    }

    public static function loadConfig(): array
    {
        if(!self::$envLoaded) {
            $dotenv = Dotenv::createImmutable(self::rootPath());
            $dotenv->load();            
        }

        if(!file_exists(self::configPath())) {
            throw new FileNotFoundException(self::configPath() . ' の設定ファイルは存在しません。');
        }

        $configInfo = require(self::configPath());
        assert(is_array($configInfo));
        // var_dump($configInfo);
        return $configInfo;
    }

    private static function rootPath()
    {
        return dirname(\Composer\Factory::getComposerFile());
    }

    public static function sqlitePath()
    {
        return self::rootPath() . DS . 'database' . DS .'database.sqlite';
    }

    public static function configPath()
    {
        $path = self::rootPath() . DS . 'config' . DS .'database.php';
        if(!realpath($path)) {
            $path = self::CONFIG_PATH;
        }
        return $path;
    }
}