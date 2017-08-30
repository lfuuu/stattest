<?php

namespace  app\classes\payments\cyberplat;

use app\classes\Singleton;

class CyberplatCrypt extends Singleton
{
    private $_privateKey = "";
    private $_publicKey = "";
    private $_passhare = "";
    private $_cyberplatPublicKey = "";

    /**
     * Initialization
     */
    public function init()
    {
        $this->_privateKey = file_get_contents(STORE_PATH . "keys/mcn_telecom__private.key");
        $this->_publicKey = file_get_contents(STORE_PATH . "keys/mcn_telecom__public.key");
        $this->_passhare = file_get_contents(STORE_PATH . "keys/mcn_telecom__passhare.key");
        $this->_cyberplatPublicKey = file_get_contents(STORE_PATH . "keys/cyberplat_public.key");
    }

    /**
     * Проверка подписи
     *
     * @param string $msg
     * @param string $signHex
     * @return bool|int
     */
    public function checkSign($msg, $signHex)
    {
        $msg = trim($msg);
        if (!($sign = @pack("H*", $signHex))) {
            return false;
        }

        $publicKey = openssl_get_publickey($this->_cyberplatPublicKey);

        return openssl_verify($msg, $sign, $publicKey);
    }

    /**
     * Подпись сообщения
     *
     * @param string $str
     * @return string
     */
    public function sign(&$str)
    {
        $pk = openssl_pkey_get_private($this->_privateKey, trim($this->_passhare));

        $sign = "";
        openssl_sign($str, $sign, $pk);
        $sign = unpack("H*", $sign);
        $str = str_replace("</response>", "<sign>" . $sign[1] . "</sign></response>", $str);

        return $str;
    }
}