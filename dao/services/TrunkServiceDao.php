<?php

namespace app\dao\services;

use app\dao\UsageDao;
use app\models\UsageTrunk;
use app\models\ClientAccount;

class TrunkServiceDao extends UsageDao
{
    public $usageClass = null;

    /**
     * Инициализация
     */
    public function init()
    {
        $this->usageClass = UsageTrunk::className();
        parent::init();
    }
    /**
     * @param ClientAccount $client
     * @return bool
     */
    public function hasService(ClientAccount $client)
    {
        return UsageTrunk::find()->where(['client_account_id' => $client->id])->count() > 0;
    }
}