<?php
namespace app\exceptions\api\internal;

use Exception;

class PartnerNotFoundException extends Exception
{
    public function __construct()
    {
        $this->message = 'PARTNER_NOT_FOUND';
        $this->code = -33;
    }
}
