<?php
namespace app\exceptions\api\internal;

use Exception;

class ExceptionValidationUsageVoip extends Exception
{

    public function __construct()
    {
        $this->message = 'NUMBER_NOT_FOUND';
        $this->code = -8;
    }

}