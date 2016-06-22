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
            && isset(\Yii::$app->params['FEEDBACK_SERVER']) && \Yii::$app->params['FEEDBACK_SERVER'];
    }

    private static function getApiUrl()
    {
        return
            self::isAvailable() ? 'https://' . \Yii::$app->params['FEEDBACK_SERVER'] . '/feedback/api/' : false;
    }

    private static function getApiKey()
    {
        return
            isset(\Yii::$app->params['FEEDBACK_API_KEY']) ?
                \Yii::$app->params['FEEDBACK_API_KEY'] :
                false;
    }


    public static function createChat($clientId, $chatId = 0, $chatName = '')
    {
        $data = [
            'account_id' => $clientId,
            'stat_product_id' => $chatId,
            'name' => $chatName ?: ($chatId ? 'Чат #' . $chatId : '')
        ];

        return self::exec('createChat', $data);
    }

    public static function removeChat($clientId, $chatId)
    {
        $data = [
            'account_id' => $clientId,
            'stat_product_id' => $chatId
        ];

        return self::exec('removeChat', $data);
    }

    public static function getChatList($clientId)
    {
        $data = [
            'account_id' => $clientId
        ];

        return self::exec('getChatList', $data);
    }

    private static function exec($action, $data = null)
    {
        if (!self::isAvailable()) {
            throw new InvalidConfigException('API Feedback was not configured');
        }

        if (!($apiKey = self::getApiKey())) {
            throw new \InvalidConfigException("FEEDBACK_API_KEY now set");
        }

        if (is_array($data)) {
            $data['api_key'] = $apiKey;
        } else {
            $data = ['api_key' => $apiKey];
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
