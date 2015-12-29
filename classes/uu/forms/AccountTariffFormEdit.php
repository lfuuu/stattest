<?php

namespace app\classes\uu\forms;

use app\classes\uu\model\AccountTariff;

class AccountTariffFormEdit extends AccountTariffForm
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
        $this->serviceTypeId = $accountTariff->service_type_id;
        return $accountTariff;
    }

    /**
     * показывать ли предупреждение, что необходимо выбрать клиента
     * @return bool
     */
    public function getIsNeedToSelectClient()
    {
        return false;
    }
}