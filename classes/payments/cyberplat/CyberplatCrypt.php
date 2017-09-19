<?php

namespace app\classes\payments\cyberplat;

use app\classes\Singleton;
use yii\base\InvalidConfigException;

class CyberplatCrypt extends Singleton
{

    private $_privateKey = "";
    private $_publicKey = "";
    private $_passhare = "";
    private $_cyberplatPublicKey = "";

    /**
     * Инициализация
     *
     * @return $this
     * @throws InvalidConfigException
     */
    public function init()
    {
        if (defined('YII_ENV') && YII_ENV == 'test') {
            return $this;
        }

        if (!isset(\Yii::$app->params['Cyberplat']) || !\Yii::$app->params['Cyberplat']) {
            throw new InvalidConfigException('Cyberplat not configured');
        }

        foreach ([
                     '_cyberplatPublicKey' => 'public_key',
                     '_privateKey' => 'mcn_private_key',
                     '_publicKey' => 'mcn_public_key',
                     '_passhare' => 'mcn_passhare'
                 ] as $property => $confKey) {

            if (!isset(\Yii::$app->params['Cyberplat'][$confKey])) {
                throw new InvalidConfigException('Cyberplat not configured (' . $confKey . ')');
            }

            $filePath = STORE_PATH . "keys/" . \Yii::$app->params['Cyberplat'][$confKey];

            if (!is_file($filePath) || !is_readable($filePath)) {
                throw new InvalidConfigException('Cyberplat not configured (file read error: ' . $confKey . ')');
            }

            $this->{$property} = file_get_contents($filePath);

            if (!$this->{$property}) {
                throw new InvalidConfigException('Cyberplat not configured (empty file: ' . $confKey . ')');
            }
        }

        return $this;
    }

    /**
     * Проверка подписи
     *
     * @param string $msg
     * @param string $signHex
     * @return bool|int
     * @throws InvalidConfigException
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
     * @throws InvalidConfigException
     */
    public function sign(&$str)
    {
        if (defined('YII_ENV') && YII_ENV == 'test') {
            return $str;
        }

        $pk = openssl_pkey_get_private($this->_privateKey, trim($this->_passhare));

        $sign = "";
        openssl_sign($str, $sign, $pk);
        $sign = unpack("H*", $sign);
        $str = str_replace("</response>", "<sign>" . $sign[1] . "</sign></response>", $str);

        return $str;
    }
}