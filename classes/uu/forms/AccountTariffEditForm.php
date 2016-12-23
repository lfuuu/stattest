<?php

namespace app\classes\uu\forms;

use app\classes\uu\model\AccountTariff;

/**
 * Class AccountTariffEditForm
 */
class AccountTariffEditForm extends AccountTariffForm
{
    /**
     * @return AccountTariff
     */
    public function getAccountTariffModel()
    {
        $accountTariffTableName = AccountTariff::tableName();

        /** @var AccountTariff $accountTariff */
        $accountTariff = AccountTariff::find()
            ->where($accountTariffTableName . '.id = :id', [':id' => $this->id])
            ->joinWith(['clientAccount', 'region', 'accountTariffLogs'])
            ->one();
        if (!$accountTariff) {
            throw new \InvalidArgumentException(\Yii::t('common', 'Wrong ID'));
        }

        $this->serviceTypeId = $accountTariff->service_type_id;
        return $accountTariff;
    }

    /**
     * Показывать ли предупреждение, что необходимо выбрать клиента
     *
     * @return bool
     */
    public function getIsNeedToSelectClient()
    {
        return false;
    }
}