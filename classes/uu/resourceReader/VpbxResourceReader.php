<?php

namespace app\classes\uu\resourceReader;

use app\classes\uu\model\AccountTariff;
use app\helpers\DateTimeZoneHelper;
use app\models\VirtpbxStat;
use DateTimeImmutable;
use yii\base\Object;

abstract class VpbxResourceReader extends Object implements ResourceReaderInterface
{
    protected $fieldName = '';

    /**
     * Вернуть количество потраченного ресурса
     *
     * @param AccountTariff $accountTariff
     * @param DateTimeImmutable $dateTime
     * @return float Если null, то данные неизвестны
     */
    public function read(AccountTariff $accountTariff, DateTimeImmutable $dateTime)
    {
        $virtpbxStat = VirtpbxStat::findOne(
            [
                'AND',
                'date' => $dateTime->format(DateTimeZoneHelper::DATE_FORMAT),
                [
                    // по услуге и клиенту, потому что в таблице virtpbx_stat все сделано костыльно
                    'OR',
                    'usage_id' => $accountTariff->id,
                    'client_id' => $accountTariff->client_account_id,
                ]
            ]
        );
        return $virtpbxStat ? $virtpbxStat->{$this->fieldName} : null;
    }

    /**
     * Как считать PricePerUnit - указана за месяц или за день
     * true - за месяц (при ежедневном расчете надо разделить на кол-во дней в месяце)
     * false - за день (при ежедневном расчете так и оставить)
     *
     * @return bool
     */
    public function getIsMonthPricePerUnit()
    {
        return true;
    }
}