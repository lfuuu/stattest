<?php
namespace app\classes\api;

use app\classes\JSONQuery;
use yii\base\Exception;

class ApiCore
{
    const ERROR_PRODUCT_NOT_EXSISTS = 538;//"Приложения 'vpbx' для лицевого счёта '####' не существует";

    public static function isAvailable() {
        return defined('CORE_SERVER') && CORE_SERVER;
    }

    public static function getApiUrl() {
        return self::isAvailable() ? 'https://' . CORE_SERVER . '/core/api/' : false;
    }

    public static function exec($action, $data) {
        if (!self::isAvailable()) {
            throw new Exception('API Core was not configured');
        }

        $result = JSONQuery::exec(self::getApiUrl() . $action, $data);

        if (isset($result["errors"]) && $result["errors"]) {

            if (isset($result["errors"]["message"]) && isset($result["errors"]["code"]))
            {
                $msg = $result["errors"]["message"];
                $code = $result["errors"]["code"];
            } else if (isset($result['errors'][0]) && isset($result['errors'][0]["message"]))
            {
                $msg = $result['errors'][0]["message"];
                $code = $result['errors'][0]["code"];
            } else {
                $msg = "Текст ошибки не найден! <br>\n" . var_export($result, true);
                $code = 500;
            }

            throw new Exception($msg, $code);
        }

        return $result;
    }
}
