<?php
namespace app\exceptions;

use yii\base\InvalidCallException;

class InvalidHttpRequestException extends InvalidCallException
{
    public $debugInfo = '';
}