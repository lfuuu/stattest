<?php

namespace app\classes\uu\resourceReader;

use app\classes\uu\model\AccountTariff;
use app\helpers\DateTimeZoneHelper;
use app\models\VirtpbxStat;
use DateTimeImmutable;
use Yii;
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
        $date = $dateTime->format(DateTimeZoneHelper::DATE_FORMAT);
        $virtpbxStat = VirtpbxStat::findOne([
            'AND',
            'date' => $date,
            [
                // по услуге и клиенту, потому что в таблице virtpbx_stat все сделано костыльно
                'OR',
                'usage_id' => $accountTariff->id,
                'client_id' => $accountTariff->client_account_id,
            ]
        ]);

        if (!$virtpbxStat) {
            Yii::error(sprintf('VpbxResourceReader. Нет данных по ресурсу %s. AccountTariffId = %d, дата = %s.', $this->fieldName, $accountTariff->id, $date));
            return null;
        }

        return $virtpbxStat->{$this->fieldName};
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