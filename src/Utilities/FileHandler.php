<?php
namespace Taro\DBModel\Utilities;

define('DS', DIRECTORY_SEPARATOR);

use Taro\DBModel\Exceptions\FileNotFoundException;

class FileHandler
{
    public const CONFIG_PATH = __DIR__ . DS . '..'.DS.'Config'.DS.'Database.php';

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
        if(!file_exists(self::CONFIG_PATH)) {
            throw new FileNotFoundException(self::CONFIG_PATH . ' の設定ファイルは存在しません。');
        }

        $configInfo = require(self::CONFIG_PATH);
        assert(is_array($configInfo));
        // var_dump($configInfo);
        return $configInfo;
    }

}