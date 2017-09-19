<?php

namespace app\classes\payments\cyberplat\exceptions;

abstract class CyberplatOk
{
    public $code = 0;
    public $message = "";
    public $data = [];

    public $authcode = null;

    private $_organizationId = null;

    /**
     * CyberplatOk constructor.
     *
     * @param string $message
     * @param int $code
     */
    public function __construct($message = '', $code = 0)
    {
        if ($message) {
            $this->message = $message;
        }

        if ($code) {
            $this->code = $code;
        }
    }

    /**
     * Установка данных
     *
     * @param array $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * Данные переводит в строку
     *
     * @return string
     */
    public function getDataStr()
    {
        $str = "";
        foreach ($this->data as $key => $value) {
            $str .= "<" . $key . ">" . $value . "</" . $key . ">\n";
        }

        return $str;
    }

    /**
     * Сообщение
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }
}