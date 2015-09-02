<?php
namespace app\classes\api;

use app\classes\JSONQuery;
use app\models\ClientAccount;
use yii\base\Exception;

class ApiPhone
{
    public static function isAvailable() {
        return isset(\Yii::$app->params['PHONE_SERVER']) && \Yii::$app->params['PHONE_SERVER'];
    }

    public static function getApiUrl() {
        return self::isAvailable() ? 'https://' . \Yii::$app->params['PHONE_SERVER'] . '/phone/api/' : false;
    }

    public static function getMultitranks()
    {
        $data = [];
        try {
            foreach(self::exec("multitrunks", null) as $d) {
                $data[$d["id"]] = $d["name"];
            }
        }catch(\Exception $e) {
            //
        }

        return $data;
    }

    public static function exec($action, $data) {
        if (!self::isAvailable()) {
            throw new Exception('API Phone was not configured');
        }

        $result = JSONQuery::exec(self::getApiUrl() . $action, $data);

        if (isset($result["errors"][0]["message"])) {
            $msg = !isset($result['errors'][0]["message"]) && isset($result['errors'][0])
                ? "Текст ошибки не найден! <br>\n" . var_export($result['errors'][0], true)
                : '';
            throw new Exception($msg ?: $result["errors"][0]["message"], $result["errors"][0]["code"]);
        }

        return $result;
    }

    public static function getNumbersInfo(ClientAccount $client)
    {
        return self::exec('numbers_info', ['account_id' => $client->id]);
    }

}
