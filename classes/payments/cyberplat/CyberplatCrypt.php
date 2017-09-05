<?php

namespace  app\classes\payments\cyberplat;

use app\classes\Singleton;
use yii\base\InvalidConfigException;

class CyberplatCrypt extends Singleton
{

    private $_organizationId = null;

    private $_privateKey = "";
    private $_publicKey = "";
    private $_passhare = "";
    private $_cyberplatPublicKey = "";

    /**
     * Устанавливаем организацию
     *
     * @param integer $organizationId
     * @return $this
     * @throws InvalidConfigException
     */
    public function setOrganization($organizationId)
    {
        $this->_organizationId = $organizationId;

        if (!$organizationId) {
            throw new InvalidConfigException('Организация не задана');
        }

        if (!isset(\Yii::$app->params['Cyberplat']) || !\Yii::$app->params['Cyberplat'] || !isset(\Yii::$app->params['Cyberplat'][$this->_organizationId])) {
            throw new InvalidConfigException('Cyberplat not configured');
        }

        $organizationConfig = \Yii::$app->params['Cyberplat'][$this->_organizationId];

        $this->_privateKey = file_get_contents(STORE_PATH . "keys/" . $organizationConfig['private_key']);
        $this->_publicKey = file_get_contents(STORE_PATH . "keys/" . $organizationConfig['public_key']);
        $this->_passhare = file_get_contents(STORE_PATH . "keys/" . $organizationConfig['passhare']);
        $this->_cyberplatPublicKey = file_get_contents(STORE_PATH . "keys/" . \Yii::$app->params['Cyberplat']['public_key']);

        return $this;
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
        if (!$this->_organizationId) {
            throw new InvalidConfigException('Организация не задана');
        }

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
        if (!$this->_organizationId) {
            throw new InvalidConfigException('Организация не задана');
        }

        $pk = openssl_pkey_get_private($this->_privateKey, trim($this->_passhare));

        $sign = "";
        openssl_sign($str, $sign, $pk);
        $sign = unpack("H*", $sign);
        $str = str_replace("</response>", "<sign>" . $sign[1] . "</sign></response>", $str);

        return $str;
    }
}