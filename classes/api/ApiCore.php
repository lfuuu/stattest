<?php
namespace app\classes\api;

use app\classes\JSONQuery;
use yii\base\Exception;

class ApiCore
{
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
            $msg = !isset($result['errors'][0]["message"]) && isset($result['errors'][0])
                    ? "Текст ошибки не найден! <br>\n" . var_export($result['errors'][0], true)
                    : '';
            throw new Exception($msg ?: $result["errors"][0]["message"], $result["errors"][0]["code"]);
        }

        return $result;
    }
}