<?php
namespace Taro\DBModel\Exceptions;

use Exception;
use Throwable;

class DatabaseConnectionException extends Exception
{
    // 例外を再定義し、メッセージをオプションではなくする
    public function __construct($message, $code = 0, Throwable $previous = null) {
        $message = 'データベース接続エラー：' . $message;
    
        // 全てを正しく確実に代入する
        parent::__construct($message, $code, $previous);
    }    
}
