<?php

namespace app\classes\payments\cyberplat\exceptions;

class AnswerErrorBadAmount extends CyberplatError
{
    public $code = 3;
    public $message = "Неверная сумма платежа";
}