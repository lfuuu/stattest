<?php
namespace app\exceptions\api\internal;

use Exception;

class ExceptionValidationDateRange extends Exception
{

    public function __construct()
    {
        $this->message = 'INVALID_DATE_RANGE';
        $this->code = -9;
    }

}