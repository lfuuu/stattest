<?php

namespace app\modules\uu\resourceReader;

use app\modules\uu\models\AccountTariff;
use DateTimeImmutable;
use yii\base\Object;

class DummyResourceReader extends Object implements ResourceReaderInterface
{
    /**
     * Вернуть количество потраченного ресурса
     *
     * @param AccountTariff $accountTariff
     * @param DateTimeImmutable $dateTime
     * @return float|null Если null, то данные неизвестны
     */
    public function read(AccountTariff $accountTariff, DateTimeImmutable $dateTime)
    {
        return null;
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