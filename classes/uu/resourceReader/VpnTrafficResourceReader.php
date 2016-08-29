<?php

namespace app\classes\uu\resourceReader;

use app\classes\uu\model\AccountTariff;
use app\models\UsageTechCpe;
use DateTimeImmutable;
use yii\base\Object;

class VpnTrafficResourceReader extends Object implements ResourceReaderInterface
{
    /** @var [] кэш данных */
    protected $usageToDateToValue = [];

    public function __construct()
    {
        parent::__construct();

        $minLogDatetime = AccountTariff::getMinLogDatetime();

        $usageTechCpeTableName = UsageTechCpe::tableName();
        $sql = <<<SQL
            SELECT
                usage_tech_cpe.id_service as usage_id,
                DATE(mod_traf_1d.datetime) AS `date`,
                sum(mod_traf_1d.transfer_rx)/1048576 as `in`,
                sum(mod_traf_1d.transfer_tx)/1048576 as `out`
            FROM
                {$usageTechCpeTableName} usage_tech_cpe,
                mod_traf_1d
            WHERE
                INET_ATON(usage_tech_cpe.ip) = mod_traf_1d.ip_int
                AND mod_traf_1d.datetime BETWEEN usage_tech_cpe.actual_from AND usage_tech_cpe.actual_to
                AND mod_traf_1d.datetime >= :date
            GROUP BY
                usage_tech_cpe.id_service,
                DATE(mod_traf_1d.datetime)
SQL;
        $db = UsageTechCpe::getDb();
        $dataReader = $db->createCommand($sql, [':date' => $minLogDatetime->format('Y-m-d')])
            ->query();
        foreach ($dataReader as $row) {
            $usageId = $row['usage_id'];
            $date = $row['date'];
            $value = (int)$row['in'] + (int)$row['in'];

            !isset($this->usageToDateToValue[$usageId]) && ($this->usageToDateToValue[$usageId] = []);
            $this->usageToDateToValue[$usageId][$date] = $value;
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
        $usageId = $accountTariff->getNonUniversalId();
        $date = $dateTime->format('Y-m-d');
        return
            isset($this->usageToDateToValue[$usageId][$date]) ?
                $this->usageToDateToValue[$usageId][$date] :
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