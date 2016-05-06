<?php

namespace app\classes\uu\resourceReader;

use app\classes\uu\model\AccountTariff;
use app\models\VirtpbxStat;
use DateTimeImmutable;
use yii\base\Object;

abstract class VpbxResourceReader extends Object implements ResourceReaderInterface
{
    protected $fieldName = '';

    /** @var [] кэш данных */
    protected $usageToDateToValue = [];
    protected $clientToDateToValue = [];

    public function __construct()
    {
        parent::__construct();

        $minLogDatetime = AccountTariff::getMinLogDatetime();
        $virtpbxStatQuery = VirtpbxStat::find()
            ->where(['>=', 'date', $minLogDatetime->format('Y-m-d')]);

        /** @var VirtpbxStat $virtpbxStat */
        foreach ($virtpbxStatQuery->each() as $virtpbxStat) {
            $usageId = $virtpbxStat->usage_id;
            $clientId = $virtpbxStat->client_id;
            $date = $virtpbxStat->date;

            !isset($this->usageToDateToValue[$usageId]) && ($this->usageToDateToValue[$usageId] = []);
            !isset($this->clientToDateToValue[$clientId]) && ($this->clientToDateToValue[$clientId] = []);

            // записать сразу в два кэша (по услуге и клиенту), потому что в таблице virtpbx_stat все сделано костыльно
            $this->clientToDateToValue[$clientId][$date] = $this->usageToDateToValue[$usageId][$date] = $virtpbxStat->{$this->fieldName};
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
        $usageId = $accountTariff->id;
        $clientId = $accountTariff->client_account_id;
        $date = $dateTime->format('Y-m-d');
        return
            // по-новому (через услугу)
            isset($this->usageToDateToValue[$usageId][$date]) ?
                $this->usageToDateToValue[$usageId][$date] :
                (
                    // по-старому (через клиента)
                isset($this->clientToDateToValue[$clientId][$date]) ?
                    $this->clientToDateToValue[$clientId][$date] :
                    null
                );
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