<?php
namespace app\classes\api;

use app\classes\HttpClient;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use Yii;
use yii\base\InvalidConfigException;

class ApiPhone
{
    /**
     * @return bool
     */
    public static function isAvailable()
    {
        return isset(Yii::$app->params['PHONE_SERVER']) && Yii::$app->params['PHONE_SERVER'];
    }

    /**
     * @return bool|string
     */
    public static function getApiUrl()
    {
        return self::isAvailable() ? 'https://' . Yii::$app->params['PHONE_SERVER'] . '/phone/api/' : false;
    }

    /**
     * @return array
     */
    public static function getApiAuthorization()
    {
        return isset(Yii::$app->params['VPBX_API_AUTHORIZATION']) ? Yii::$app->params['VPBX_API_AUTHORIZATION'] : [];
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
            ->auth(self::getApiAuthorization())
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

    /**
     * @param ClientAccount $clientAccount
     * @param int $did
     * @param \DateTimeImmutable $date
     * @return array. int_number_amount=>... или errors=>...
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\base\InvalidCallException
     * @throws \yii\base\InvalidConfigException
     */
    public static function getResourceVoipLines(ClientAccount $clientAccount, $did, \DateTimeImmutable $date)
    {
        return self::exec(
            'get_did_client_lines',
            [
                'timezone' => $clientAccount->timezone_name,
                'date_log' => $date->format(DateTimeZoneHelper::DATE_FORMAT),
                'account_id' => $clientAccount->id,
                'did' => $did,
            ]
        );
    }

}
