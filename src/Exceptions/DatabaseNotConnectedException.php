<?php
namespace Taro\DBModel\Exceptions;

use Exception;
use Throwable;

class DatabaseNotConnectedException extends Exception
{
    // 例外を再定義し、メッセージをオプションではなくする
    public function __construct($dbName, $code = 0, Throwable $previous = null) {
        $message = 'データベース：' . $dbName . 'とは接続されていません。';
    
        // 全てを正しく確実に代入する
        parent::__construct($message, $code, $previous);
    }    
}
