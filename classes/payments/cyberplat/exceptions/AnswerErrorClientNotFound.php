<?php

namespace app\classes\payments\cyberplat\exceptions;

class AnswerErrorClientNotFound extends CyberplatError
{
    public $code = 2;
    public $message = "Лицевой счет не найден";
}