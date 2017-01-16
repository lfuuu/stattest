<?php
namespace app\classes\api;

use app\classes\HttpClient;
use app\models\ClientAccount;
use yii\base\InvalidConfigException;

class ApiPhone
{
    /**
     * @return bool
     */
    public static function isAvailable()
    {
        return isset(\Yii::$app->params['PHONE_SERVER']) && \Yii::$app->params['PHONE_SERVER'];
    }

    /**
     * @return bool|string
     */
    public static function getApiUrl()
    {
        return self::isAvailable() ? 'https://' . \Yii::$app->params['PHONE_SERVER'] . '/phone/api/' : false;
    }

    /**
     * @return array
     */
    public static function getMultitranks()
    {
        $data = [];
        try {
            foreach (self::exec("multitrunks", null) as $d) {
                $data[$d["id"]] = $d["name"];
            }
        } catch (\Exception $e) {
            // так исторически сложилось
        }

        return $data;
    }

    /**
     * @param string $action
     * @param array $data
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\base\InvalidCallException
     */
    public static function exec($action, $data)
    {
        if (!self::isAvailable()) {
            throw new InvalidConfigException('API Phone was not configured');
        }

        return (new HttpClient)
            ->createJsonRequest()
            ->setMethod('post')
            ->setData($data)
            ->setUrl(self::getApiUrl() . $action)
            ->getResponseDataWithCheck();
    }

    /**
     * @param ClientAccount $client
     * @return array
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\base\InvalidCallException
     */
    public static function getNumbersInfo(ClientAccount $client)
    {
        return self::exec('numbers_info', ['account_id' => $client->id]);
    }

}
