<?php
namespace app\classes\api;

use app\classes\HandlerLogger;
use app\classes\HttpClient;
use yii\base\InvalidConfigException;

class ApiFeedback
{
    /**
     * @return bool
     */
    public static function isAvailable()
    {
        return
            isset(\Yii::$app->params['CORE_SERVER']) && \Yii::$app->params['CORE_SERVER']
            && isset(\Yii::$app->params['FEEDBACK_SERVER']) && \Yii::$app->params['FEEDBACK_SERVER'];
    }

    /**
     * @return bool|string
     */
    private static function _getApiUrl()
    {
        return self::isAvailable() ? 'https://' . \Yii::$app->params['FEEDBACK_SERVER'] . '/feedback/api/' : false;
    }

    /**
     * @return bool
     */
    private static function _getApiKey()
    {
        return isset(\Yii::$app->params['FEEDBACK_API_KEY']) ? \Yii::$app->params['FEEDBACK_API_KEY'] : false;
    }


    /**
     * @param int $clientId
     * @param int $chatId
     * @return mixed
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\base\InvalidCallException
     */
    public static function createChat($clientId, $chatId = 0)
    {
        $data = [
            'account_id' => $clientId,
            'stat_product_id' => $chatId,
            'name' => 'Chat ' . $chatId
        ];

        return self::_exec('createChat', $data);
    }

    /**
     * @param int $clientId
     * @param int $chatId
     * @return mixed
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\base\InvalidCallException
     */
    public static function updateChat($clientId, $chatId)
    {
        $data = [
            'account_id' => $clientId,
            'stat_product_id' => $chatId
        ];

        return self::_exec('updateChat', $data);
    }

    /**
     * @param int $clientId
     * @param int $chatId
     * @return mixed
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\base\InvalidCallException
     */
    public static function removeChat($clientId, $chatId)
    {
        $data = [
            'account_id' => $clientId,
            'stat_product_id' => $chatId
        ];

        return self::_exec('removeChat', $data);
    }

    /**
     * @param int $clientId
     * @return mixed
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\base\InvalidCallException
     */
    public static function getChatList($clientId)
    {
        $data = [
            'account_id' => $clientId
        ];

        return self::_exec('getChatList', $data);
    }

    /**
     * @param string $action
     * @param array $data
     * @return mixed
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\base\InvalidCallException
     * @throws InvalidConfigException
     */
    private static function _exec($action, $data = null)
    {
        HandlerLogger::me()->add('API Feedback is off');

        return true;

        if (!self::isAvailable()) {
            throw new InvalidConfigException('API Feedback was not configured');
        }

        if (!($apiKey = self::_getApiKey())) {
            throw new InvalidConfigException('FEEDBACK_API_KEY now set');
        }

        if (is_array($data)) {
            $data['api_key'] = $apiKey;
        } else {
            $data = ['api_key' => $apiKey];
        }

        return (new HttpClient)
            ->createJsonRequest()
            ->setMethod('post')
            ->setData($data)
            ->setUrl(self::_getApiUrl() . $action)
            ->getResponseDataWithCheck();
    }

}
