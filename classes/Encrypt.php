<?php

namespace app\classes;


use Yii;

/**
 * Class Encrypt
 * Шифрует и расшифровывет строки и массивы.
 * Позволяет зашифровать данные и отправить на сторону клиента, с возможностью их обратной дешифровки, но без возможности их изменить.
 *
 * @package app\classes
 */
class Encrypt
{
    const KEY_SECTION_DEFAULT = 'UDATA';

    /**
     * Возвращает ключ по имени секции
     *
     * @param string|null $keySection секция
     * @return string
     */
    private static function getKey($keySection = null)
    {
        if (!$keySection) {
            $keySection = self::KEY_SECTION_DEFAULT;
        }

        if (!isset(Yii::$app->params['encrypt'][$keySection])) {
            $keySection = self::KEY_SECTION_DEFAULT;
        }

        return Yii::$app->params['encrypt'][$keySection];
    }

    /**
     * Кодирует строку
     *
     * @param string $data
     * @param string|null $keySection
     * @return string
     */
    public static function encodeString($data, $keySection = null)
    {
        $d = substr(md5($data), 0, 1);
        if (($d < '0') || ($d > '9')) {
            $di = 10 + ord($d) - ord('a');
            if ($di >= 16) {
                $di = 0;
            }
        } else {
            $di = ord($d) - ord('0');
        }
        $data2 = "";
        $key = self::getKey($keySection);
        $l2 = strlen($key);
        for ($i = 0; $i < strlen($data); $i++) {
            $v = (ord($data[$i]) + ord($key[($i + $di) % $l2])) % 256;
            $data2 .= chr($v);
        }
        return urlencode(base64_encode($data2) . $d);
    }

    /**
     * Декодирует строку
     *
     * @param string $data
     * @param string|null $keySection
     * @return string
     */
    public static function decodeString($data, $keySection = null)
    {
        $di = substr($data, strlen($data) - 1, 1);
        $data = substr($data, 0, strlen($data) - 1);
        if (($di < '0') || ($di > '9')) {
            $di = 10 + ord($di) - ord('a');
            if ($di >= 16) {
                $di = 0;
            }
        } else {
            $di = ord($di) - ord('0');
        }

        $data = base64_decode($data);
        $data2 = "";

        $key = self::getKey($keySection);

        $l2 = strlen($key);
        for ($i = 0; $i < strlen($data); $i++) {
            $data2 .= chr((ord($data[$i]) + 256 - ord($key[($i + $di) % $l2])) % 256);
        }
        return $data2;
    }

    /**
     * Кодирует массив
     *
     * @param array $arr
     * @param string|null $keySection
     * @return string
     */
    public static function encodeArray($arr, $keySection = null)
    {
        $s = '';
        foreach ($arr as $k => $v) {
            if ($s) {
                $s .= '|';
            }
            $s .= $k . '=' . $v;
        }
        return self::encodeString($s, $keySection);
    }

    /**
     * Декодирует масив
     *
     * @param string $data
     * @param string|null $keySection
     * @return array|null
     */
    public static function decodeToArray($data, $keySection = null)
    {
        $v = explode('|', self::decodeString($data, $keySection));
        if (!count($v)) {
            return null;
        }
        $R = array();
        foreach ($v as $vi) {
            $vi = explode('=', $vi);
            if (count($vi) == 2) {
                $R[$vi[0]] = $vi[1];
            }
        }
        return $R;
    }

    /**
     * Кодирует ссылку на PDF
     *
     * @param string $type invoice|act|bill
     * @param app\models\Invoice $invoice
     * @return string
     */
    public static function encodePdfLink($type, $invoice)
    {
        $result = false;

        $url = \Yii::$app->params['SITE_URL'] . 'bill.php?bill=';

        switch ($type) {
            case 'invoice':
                $result = $url . Encrypt::encodeArray([
                    'tpl' => 1,
                    'is_pdf' => 1,
                    'client' => $invoice->bill->client_id,
                    'invoice_id' => $invoice->id,
                    'is_act' => 0,
                ]);
            break;
            case 'act':
                $result = $url . Encrypt::encodeArray([
                    'tpl' => 1,
                    'is_pdf' => 1,
                    'client' => $invoice->bill->client_id,
                    'invoice_id' => $invoice->id,
                    'is_act' => 1,
                ]);
            break;
            case 'bill':
                $result = $url . Encrypt::encodeArray([
                    'bill' => $invoice->bill->bill_no,
                    'object' => 'bill-2-RUB',
                    'client' => $invoice->bill->client_id,
                    'is_pdf' => 1,
                ]);
            break;
        }

        return $result;
    }
}
