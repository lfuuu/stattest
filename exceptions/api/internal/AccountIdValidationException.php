<?php
namespace app\exceptions\api\internal;

use Exception;

class AccountIdValidationException extends Exception
{

    public function __construct()
    {
        $this->message = 'ACCOUNT_NOT_FOUND';
        $this->code = -7;
    }

}