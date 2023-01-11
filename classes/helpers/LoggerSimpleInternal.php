<?php

namespace app\classes\helpers;

/**
 * Класс реализующий логирование
 */
class LoggerSimpleInternal
{
    private $_log = [];

    public function add($text):void
    {
        $this->_log[] = $text;
    }

    public function get():string
    {
        return implode("\n", $this->_log) . PHP_EOL;
    }
}