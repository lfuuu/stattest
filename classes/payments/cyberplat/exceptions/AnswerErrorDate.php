<?php

namespace app\classes\payments\cyberplat\exceptions;

class AnswerErrorDate extends CyberplatError
{
    public $code = 5;
    public $message = "Неверное значение даты";
}