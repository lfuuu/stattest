<?php
namespace app\classes\api;

use app\classes\HttpClient;
use yii\base\InvalidConfigException;

class ApiChatBot
{
    /**
     * @return bool
     */
    public static function isAvailable()
    {
        return isset(\Yii::$app->params['CHAT_BOT_SERVER']) && \Yii::$app->params['CHAT_BOT_SERVER'];
    }

    /**
     * @return bool|string
     */
    private static function _getApiUrl()
    {
        return self::isAvailable() ? 'https://' . \Yii::$app->params['CHAT_BOT_SERVER'] . '/api/private/api/' : false;
    }


    /**
     * @param int $clientId
     * @param int $serviceId
     * @param int $tariffId
     * @return mixed
     * @throws InvalidConfigException
     * @throws \yii\web\BadRequestHttpException
     */
    public static function createChatBot($clientId, $serviceId, $tariffId)
    {
        // { account_id: НОМЕР_ЛС, service_id: НОМЕР_УСЛУГИ, max_scenario_number: КОЛИЧЕСТВО_БОТОВ(Пока хардкодим 3) }
        $data = [
            'account_id' => $clientId,
            'service_id' => $serviceId,
            'tariff_id' => $tariffId,
            'max_scenario_number' => 3,
        ];

        return self::_exec('set_service', $data);
    }

    /**
     * @param int $serviceId
     * @return mixed
     * @throws InvalidConfigException
     * @throws \yii\web\BadRequestHttpException
     */
    public static function removeChatBot($serviceId)
    {
        $data = [
            'service_id' => $serviceId,
        ];

        return self::_exec('delete_service', $data);
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
        if (!self::isAvailable()) {
            throw new InvalidConfigException('API ChatBot was not configured');
        }

        return (new HttpClient)
            ->createJsonRequest()
            ->setMethod('post')
            ->setData($data)
            ->setUrl(self::_getApiUrl() . $action)
            ->getResponseDataWithCheck();
    }

}
