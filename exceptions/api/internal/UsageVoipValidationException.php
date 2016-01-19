<?php
namespace app\exceptions\api\internal;

use Exception;

class UsageVoipValidationException extends Exception
{

    public function __construct()
    {
        $this->message = 'NUMBER_NOT_FOUND';
        $this->code = -8;
    }

}