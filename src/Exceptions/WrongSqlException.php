<?php
namespace Taro\DBModel\Exception;

use Exception;
use Throwable;

class WrongSqlException extends Exception
{
    // 例外を再定義し、メッセージをオプションではなくする
    public function __construct($rawSql, $code = 0, Throwable $previous = null) {
        $message = '作成されたSQL:' . $rawSql . 'に問題があります。';

        // 全てを正しく確実に代入する
        parent::__construct($message, $code, $previous);
    }  
    
}
    
