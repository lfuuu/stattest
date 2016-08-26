<?php

namespace app\classes\uu\resourceReader;

use app\classes\uu\model\AccountTariff;
use app\models\SmsStat;
use DateTimeImmutable;
use yii\base\Object;

class SmsResourceReader extends Object implements ResourceReaderInterface
{
    /** @var [] кэш данных */
    protected $clientToDateToValue = [];

    public function __construct()
    {
        parent::__construct();

        $minLogDatetime = AccountTariff::getMinLogDatetime();
        $smsStatQuery = SmsStat::find()
            ->where(['>=', 'date', $minLogDatetime->format('Y-m-d')]);

        /** @var SmsStat $smsStat */
        foreach ($smsStatQuery->each() as $smsStat) {
            $clientId = $smsStat->sender;
            $date = $smsStat->date_hour;

            !isset($this->clientToDateToValue[$clientId]) && ($this->clientToDateToValue[$clientId] = []);
            $this->clientToDateToValue[$clientId][$date] = $smsStat->count;
        }
    }

    /**
     * Вернуть количество потраченного ресурса
     *
     * @param AccountTariff $accountTariff
     * @param DateTimeImmutable $dateTime
     * @return float Если null, то данные неизвестны
     */
    public function read(AccountTariff $accountTariff, DateTimeImmutable $dateTime)
    {
        $clientId = $accountTariff->client_account_id;
        $date = $dateTime->format('Y-m-d');
        return
            isset($this->clientToDateToValue[$clientId][$date]) ?
                $this->clientToDateToValue[$clientId][$date] :
                null;
    }

    /**
     * Как считать PricePerUnit - указана за месяц или за день
     * true - за месяц (при ежедневном расчете надо разделить на кол-во дней в месяце)
     * false - за день (при ежедневном расчете так и оставить)
     * @return bool
     */
    public function getIsMonthPricePerUnit()
    {
        return true;
    }
}