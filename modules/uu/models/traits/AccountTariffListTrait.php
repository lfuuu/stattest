<?php

namespace app\modules\uu\models\traits;

use app\classes\helpers\ArrayHelper;
use app\classes\traits\GetListTrait;
use app\models\billing\ServiceTrunk;
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

    /**
     * Вернуть список Узлов калиграфии
     *
     * @param int $regionId
     * @param bool $isWithEmpty
     * @param bool $isWithNullAndNotNull
     * @return array
     * @throws \yii\db\Exception
     */
    public static function getCalligrapherNodeList($regionId, $isWithEmpty = true, $isWithNullAndNotNull = false)
    {
        $list = [];
        if ($regionId) {
            $list = ServiceTrunk::getDb()->createCommand("SELECT t.node_id, t.node_name_id FROM calligrapher.node t WHERE region_id=:regionId ORDER BY node_name_id", ['regionId' => $regionId])->queryAll();
        }

        $list = array_combine(
            array_map(fn($l) => $l['node_id'], $list),
            array_map(fn($l) => $l['node_name_id'], $list)
        );

        return GetListTrait::getEmptyList($isWithEmpty, $isWithNullAndNotNull) + $list;
    }

    /**
     * Вернуть список Типов подключений калиграфии
     *
     * @param bool $isWithEmpty
     * @param bool $isWithNullAndNotNull
     * @return array
     * @throws \yii\db\Exception
     */
    public static function getCalligrapherTypeConnectionList($isWithEmpty = true, $isWithNullAndNotNull = false)
    {
        $list = ServiceTrunk::getDb()->createCommand("SELECT t.type_connection_id, t.type_connection FROM calligrapher.type_connection t ORDER BY t.type_connection")->queryAll();

        $list = array_combine(
            array_map(fn($l) => $l['type_connection_id'], $list),
            array_map(fn($l) => $l['type_connection'], $list)
        );

        return GetListTrait::getEmptyList($isWithEmpty, $isWithNullAndNotNull) + $list;
    }
}