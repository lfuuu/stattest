<?php

namespace app\classes\payments\cyberplat\exceptions;

class AnswerErrorSign extends CyberplatError
{
    public $code = -4;
    public $message = "Ошибка проверки АСП";
}