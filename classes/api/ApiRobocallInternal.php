<?php

namespace app\classes\api;

use app\classes\Singleton;
use app\classes\HttpClient;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ResourceModel;
use app\modules\uu\models\ServiceType;
use Exception;
use Yii;
use yii\base\InvalidConfigException;

/**
 * Class ApiRobocallInternal
 */
class ApiRobocallInternal extends Singleton
{
    /**
     * @return bool
     */
    public function isAvailable()
    {
        return isset(Yii::$app->params['ROBOCALL_INTERNAL_SERVER']) && Yii::$app->params['ROBOCALL_INTERNAL_SERVER'];
    }

    /**
     * @return bool|string
     */
    public function getApiUrl()
    {
        return $this->isAvailable() ? Yii::$app->params['ROBOCALL_INTERNAL_SERVER'] : false;
    }

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
        if (!$this->isAvailable()) {
            throw new InvalidConfigException('API Robocall was not configured');
        }

        return (new HttpClient)
            ->createJsonRequest()
            ->setMethod($isPostJSON ? 'post' : 'get')
            ->setData($data)
            ->setUrl(self::getApiUrl() . $action)
            ->getResponseDataWithCheck();
    }

    /**
     * @param int $clientId
     * @param int $serviceId
     * @param bool $is_carousel
     * @param int $stat_product_id
     * @param int $max_channels
     * @return mixed
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\base\InvalidCallException
     */
    public function create($clientId, $serviceId = 0)
    {
        $accountTariff = AccountTariff::findOne(['id' => $serviceId]);

        if (!$accountTariff) {
            throw new Exception('tariff doesn\'t exist');
        }

        if ($accountTariff->service_type_id != ServiceType::ID_VOICE_ROBOT) {
            throw new Exception('Wrong service type id');
        }
        
        // { account_id: НОМЕР_ЛС, service_id: НОМЕР_УСЛУГИ, is_carousel: подключена ли карусель (пока false), stat_product_id: номер услуги, max_channels: максимальное количество каналов на услуге }
        $data = [
            'account_id' => $clientId,
            'service_id' => $serviceId,
            'is_carousel' => (bool)$accountTariff->getResourceValue(ResourceModel::ID_VR_CAROUSEL),
            'stat_product_id' => $accountTariff->id,
            'max_channels' => $accountTariff->getResourceValue(ResourceModel::ID_VR_CHANNEL_COUNT),
        ];

        return $this->exec('/api/private/api/set_service', $data);
    }

    public function update($clientId, $serviceId)
    {
        $this->create($clientId, $serviceId);
    }

    /**
     * @param int $serviceId
     * @return mixed
     * @throws InvalidConfigException
     * @throws \yii\web\BadRequestHttpException
     */
    public function remove($serviceId)
    {
        $data = [
            'service_id' => $serviceId,
        ];

        return self::exec('/api/private/api/delete_service', $data);
    }

}
