<?php
namespace app\classes\api;

use app\classes\JSONQuery;
use yii\base\InvalidConfigException;

class ApiFeedback
{
    private static function isAvailable()
    {
        return
            isset(\Yii::$app->params['CORE_SERVER']) && \Yii::$app->params['CORE_SERVER']
            && isset(\Yii::$app->params['FEEDBACK_PRODUCT_ENABLED']) && \Yii::$app->params['FEEDBACK_PRODUCT_ENABLED'];
    }

    private static function getApiUrl()
    {
        return
            self::isAvailable() ? 'https://' . \Yii::$app->params['CORE_SERVER'] . '/feedback/api/' : false;
    }

    public static function createChat($clientId, $chatId = 0, $chatName = '')
    {
        $data = [
            'account_id' => $clientId,
            'name' => $chatName ?: ($chatId ? 'Чат #' . $chatId : '')
        ];

        return self::exec('createChat', $data);
    }

    public static function removeChat($clientId, $chatId)
    {
        //TODO реализовать когда feedback/chat будет доработан
        return true;

        /*
        $data = [
            'account_id' => $clientId,
            'chat_id' => $chatId
        ];

        return self::exec('removeChat', $data);
        */
    }

    public static function getChatList($clientId)
    {
        $data = [
            'account_id' => $clientId
        ];

        return self::exec('getChatList', $data);
    }

    private static function exec($action, $data)
    {
        if (!self::isAvailable()) {
            throw new InvalidConfigException('API Feedback was not configured');
        }

        $result = JSONQuery::exec(self::getApiUrl() . $action, $data);

        if (isset($result["errors"]) && $result["errors"]) {

            if (isset($result["errors"]["message"]) && isset($result["errors"]["code"])) {
                $msg = $result["errors"]["message"];
                $code = $result["errors"]["code"];
            } else {
                if (isset($result['errors'][0]) && isset($result['errors'][0]["message"])) {
                    $msg = $result['errors'][0]["message"];
                    $code = $result['errors'][0]["code"];
                } else {
                    $msg = "Текст ошибки не найден! <br>\n" . var_export($result, true);
                    $code = 500;
                }
            }

            throw new \HttpResponseException($msg, $code);
        }

        return $result;
    }

}
