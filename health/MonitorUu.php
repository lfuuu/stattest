<?php

namespace app\health;

use app\helpers\DateTimeZoneHelper;
use app\modules\uu\models\AccountEntry;
use app\modules\uu\models\traits\AccountTariffBillerTrait;

abstract class MonitorUu extends Monitor
{
    /**
     * Текущее значение
     *
     * @param string $sql
     * @return int
     * @throws \yii\db\Exception
     */
    protected function getValueNySql($sql)
    {
        $dateFrom = AccountTariffBillerTrait::getMinLogDatetime()
            ->format(DateTimeZoneHelper::DATE_FORMAT);
        $db = AccountEntry::getDb();
        return $db->createCommand($sql, [':date_from' => $dateFrom])->queryScalar();
    }
}