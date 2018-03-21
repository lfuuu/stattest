<?php

namespace app\modules\uu\models\traits;

use app\classes\traits\GetListTrait;
use app\modules\uu\models\AccountTariff;

trait AccountTariffListTrait
{
    /**
     * Вернуть список всех доступных значений
     *
     * @param bool|string $isWithEmpty false - без пустого, true - с '----', string - с этим значением
     * @param bool $isWithNullAndNotNull
     * @return string[]
     */
    public static function getTrunkTypeList($isWithEmpty = false, $isWithNullAndNotNull = false)
    {
        return GetListTrait::getEmptyList($isWithEmpty, $isWithNullAndNotNull) + [
                AccountTariff::TRUNK_TYPE_MEGATRUNK => 'Мегатранк',
                AccountTariff::TRUNK_TYPE_MULTITRUNK => 'Мультитранк',
            ];
    }

    /**
     * Вернуть список всех номеров, привязанных к client_account_id таблицы AccountTariff
     *
     * @param string $accountId
     * @return array
     */
    public static function getVoipListByClientAccountId($accountId)
    {
        return AccountTariff::find()
            ->select('voip_number')
            ->indexBy('voip_number')
            ->where(['client_account_id' => $accountId,])
            ->andWhere(['NOT', ['voip_number' => null]])
            ->column();
    }
}