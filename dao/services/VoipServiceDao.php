<?php

namespace app\dao\services;

use app\dao\UsageDao;
use Yii;
use app\models\TariffVoip;
use app\models\ClientAccount;
use app\models\UsageVoip;

class VoipServiceDao extends UsageDao
{
    public $usageClass = null;

    /**
     * Инициализация
     */
    public function init()
    {
        $this->usageClass = UsageVoip::className();

        parent::init();
    }

    public function getTariffsList(ClientAccount $client, $region)
    {
        return
            TariffVoip::find()
                ->where(['currency_id' => $client->currency])
                ->andWhere(['connection_point_id' => $region])
                ->andWhere(['!=', 'status', 'archive'])
                ->all();
    }

    public function getNextLineNumber()
    {
        return Yii::$app->db->createCommand("
            select max(CONVERT(E164,UNSIGNED INTEGER))+1 as number from usage_voip where LENGTH(E164)>=4 and LENGTH(E164)<=5 and E164 not in ('7495', '7499')
            ")->queryScalar() ?: "1000";
    }

    public function hasService(ClientAccount $client)
    {
        return UsageVoip::find()
            ->client($client->client)
            ->actual()
            ->count() > 0;
    }

}
