<?php

namespace app\classes\payments\cyberplat\exceptions;

class AnswerErrorStatus extends CyberplatError
{
    public $code = 6;
    public $message = "Успешный платеж с таким номером не найден";
}