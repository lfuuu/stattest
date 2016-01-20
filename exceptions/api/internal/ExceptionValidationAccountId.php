<?php
namespace app\exceptions\api\internal;

use yii\base\Exception;

class ExceptionValidationAccountId extends Exception
{

    public function __construct()
    {
        $this->message = 'ACCOUNT_NOT_FOUND';
        $this->code = -7;
    }

}