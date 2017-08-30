<?php

namespace app\classes\payments\cyberplat\exceptions;

class AnswerErrorAction extends CyberplatError
{
    public $code = 1;
    public $message = "Неизвестный тип запроса";
}