<?php

namespace app\dao\services;

use app\dao\UsageDao;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;
use Yii;
use app\models\TariffVoip;
use app\models\ClientAccount;
use app\models\UsageVoip;

/**
 * Class VoipServiceDao
 */
class VoipServiceDao extends UsageDao
{
    public $usageClass = null;

    /**
     * Инициализация
     */
    public function init()
    {
        $this->usageClass = UsageVoip::class;

        parent::init();
    }

    /**
     * Список тарифов
     *
     * @param ClientAccount $client
     * @param integer $region
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getTariffsList(ClientAccount $client, $region)
    {
        return
            TariffVoip::find()
                ->where(['currency_id' => $client->currency])
                ->andWhere(['connection_point_id' => $region])
                ->andWhere(['!=', 'status', 'archive'])
                ->all();
    }

    /**
     * Получение следующего номера линии-без-номера
     *
     * @return string
     */
    public function getNextLineNumber()
    {
        return
            (int) max(
                Yii::$app->db->createCommand("SELECT MAX(CONVERT(E164,UNSIGNED INTEGER))+1 AS number FROM usage_voip WHERE LENGTH(E164) BETWEEN 4 AND 5 AND E164 NOT IN ('7495', '7499')")->queryScalar() ?: 1000,
                Yii::$app->db->createCommand("SELECT MAX(CONVERT(voip_number,UNSIGNED INTEGER))+1 AS NUMBER FROM uu_account_tariff WHERE LENGTH(voip_number) BETWEEN 4 AND 5")->queryScalar() ?: 1000
                );
    }

    /**
     * Есть ли услуги телефонии у ЛС
     *
     * @param ClientAccount $client
     * @return bool
     */
    public function hasService(ClientAccount $client)
    {
        return UsageVoip::find()
            ->client($client->client)
            ->actual()
            ->count() > 0;
    }

    /**
     * Включена ли хоть одна услуга номера на ЛС
     *
     * @param integer|ClientAccount $account
     * @return bool
     */
    public function isVoipExists($account)
    {
        if (!($account instanceof ClientAccount)) {
            $account = ClientAccount::findOne(['id' => (int)$account]);
        }

        if (!$account) {
            throw new \LogicException('account not found');
        }

        return
            UsageVoip::find()->where(['client' => $account->client])->exists()
            || AccountTariff::isServiceExists($account->id, ServiceType::ID_VOIP);

    }

}
