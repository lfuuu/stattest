<?php

namespace app\classes\api;

use app\classes\Singleton;
use app\classes\HttpClient;
use Yii;
use yii\base\InvalidConfigException;

/**
 * Class ApiBase
 */
class ApiBase extends Singleton
{
    /**
     * @return bool
     */
    public function isAvailable()
    {
        return isset(Yii::$app->params['BASE_SERVER']) && Yii::$app->params['BASE_SERVER'];
    }

    /**
     * @return bool|string
     */
    public function getApiUrl()
    {
        return $this->isAvailable() ? 'https://' . Yii::$app->params['BASE_SERVER'] . '/api/private/api/' : false;
    }

    /**
     * @return array
     */
//    public static function getApiAuthorization()
//    {
//        return isset(Yii::$app->params['AUTHORIZATION']) ? Yii::$app->params['AUTHORIZATION'] : [];
//    }

    /**
     * @param string $action
     * @param array $data
     * @param bool $isPostJSON
     * @return mixed
     * @throws InvalidConfigException
     * @throws \yii\base\InvalidCallException
     * @throws \yii\web\BadRequestHttpException
     */
    private function exec($action, $data, $isPostJSON = true)
    {
        if (!self::isAvailable()) {
            throw new InvalidConfigException('API Core was not configured');
        }

        return (new HttpClient)
            ->createJsonRequest()
            ->setMethod($isPostJSON ? 'post' : 'get')
            ->setData($data)
            ->setUrl(self::getApiUrl() . $action)
//            ->auth(self::getApiAuthorization())
            ->getResponseDataWithCheck();
    }

    public function syncStatClientStructure($data)
    {
        return $this->exec('sync/statClientStructure', $data);
    }

    public function userCreateCoreOwner($email, $fullName, $contractId = 0)
    {
        return $this->exec('user/createCoreOwner', [
            'email' => $email,
            'fullName' => $fullName,
            'contractId' => $contractId,
        ]);
    }
}
