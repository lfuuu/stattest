<?php

namespace app\classes\payments\cyberplat\exceptions;

class CyberplatError extends \Exception
{
    public $code = 10;
    public $message = "";
}