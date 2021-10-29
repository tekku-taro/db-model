<?php
namespace Taro\DBModel\Exceptions;

use Exception;
use Throwable;

class InvalidDataException extends Exception
{
    // 例外を再定義し、メッセージをオプションではなくする
    public function __construct($reason, $code = 0, Throwable $previous = null) {
        $message = 'データに問題があります。:' . $reason;

        // 全てを正しく確実に代入する
        parent::__construct($message, $code, $previous);
    }  
    
}
    
