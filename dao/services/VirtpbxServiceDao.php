<?php

namespace app\dao\services;

use app\models\UsageVirtpbx;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;
use app\classes\Singleton;
use app\models\TariffVirtpbx;
use app\models\ClientAccount;

class VirtpbxServiceDao extends Singleton
{

    /**
     * @param ClientAccount $clientAccount
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getTariffsList(ClientAccount $clientAccount)
    {
        return
            TariffVirtpbx::find()
                ->where(['currency' => $clientAccount->currency])
                ->andWhere(['!=', 'status', 'archive'])
                ->all();
    }

    /**
     * Включена ли ВАТС на ЛС
     *
     * @param integer|ClientAccount $account
     * @return bool
     */
    public function isVpbxExists($account)
    {
        if (!($account instanceof ClientAccount)) {
            $account = ClientAccount::findOne(['id' => $account]);
        }

        if (!$account) {
            throw new \LogicException('account not found');
        }

        return
            UsageVirtpbx::find()->where(['client' => $account->client])->exists()
            || AccountTariff::isServiceExists($account->id, ServiceType::ID_VPBX);

    }

}
