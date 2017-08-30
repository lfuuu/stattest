<?php

namespace app\classes\payments\cyberplat\exceptions;

class AnswerErrorCancel extends CyberplatError
{
    public $code = 9;
    public $message = "Платеж не может быть отменен";
}