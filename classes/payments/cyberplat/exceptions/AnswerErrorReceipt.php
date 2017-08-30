<?php

namespace app\classes\payments\cyberplat\exceptions;

class AnswerErrorReceipt extends CyberplatError
{
    public $code = 4;
    public $message = "Неверное значение платежа";
}