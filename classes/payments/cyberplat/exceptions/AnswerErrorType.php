<?php

namespace app\classes\payments\cyberplat\exceptions;

class AnswerErrorType extends CyberplatError
{
    public $code = -2;
    public $message = "Неверное значение платежа";
}