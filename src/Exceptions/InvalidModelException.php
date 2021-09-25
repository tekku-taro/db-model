<?php
namespace Taro\DBModel\Exceptions;

use Exception;
use Throwable;

class InvalidModelException extends Exception
{
    // 例外を再定義し、メッセージをオプションではなくする
    public function __construct($sqlOrReason, $code = 0, Throwable $previous = null) {
        $message = 'モデルのデータに問題があります。:' . $sqlOrReason;

        // 全てを正しく確実に代入する
        parent::__construct($message, $code, $previous);
    }  
    
}
    
