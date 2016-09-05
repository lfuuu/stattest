<?php

namespace app\classes\uu\resourceReader;

use app\classes\uu\model\AccountTariff;
use app\models\UsageIpPorts;
use DateTimeImmutable;
use yii\base\Object;

class CollocationTrafficResourceReader extends Object implements ResourceReaderInterface
{
    protected $fieldNameIn = '';
    protected $fieldNameOut = '';

    /** @var [] кэш данных */
    protected $dateToValue = [];
    protected $accountTariffId = null;

    /**
     * Вернуть количество потраченного ресурса
     *
     * @param AccountTariff $accountTariff
     * @param DateTimeImmutable $dateTime
     * @return float Если null, то данные неизвестны
     */
    public function read(AccountTariff $accountTariff, DateTimeImmutable $dateTime)
    {
        $accountTariffId = $accountTariff->getNonUniversalId() ?: $accountTariff->id;
        $this->createCache($accountTariffId);
        $date = $dateTime->format('Y-m-d');

        return
            isset($this->dateToValue[$date]) ?
                (int)$this->dateToValue[$date][$this->fieldNameIn] + (int)$this->dateToValue[$date][$this->fieldNameOut] :
                null;
    }

    /**
     * @param int $accountTariffId
     */
    protected function createCache($accountTariffId)
    {
        if ($this->accountTariffId == $accountTariffId) {
            return;
        }

        $this->accountTariffId = $accountTariffId;
        $this->dateToValue = [];

        /**
         * Попытка самостоятельно разобраться приводит к выносу мозга, поэтому помогу ("помогу разобраться", а не "помогу вынести мозг")
         * берем услуги
         * находим сети
         * cidr (ip с маской) преобразуем в диапазон ip, попутно исправляй всякий треш
         * по статистике ip смотрим трафик
         */
        $sql = <<<SQL
            SELECT
                DATE(traf_flows_1d.time) AS `date`,
                sum(traf_flows_1d.in_r)/1048576 as `in_r`,
                sum(traf_flows_1d.out_r)/1048576 as `out_r`,
                sum(traf_flows_1d.in_r2)/1048576 as `in_r2`,
                sum(traf_flows_1d.out_r2)/1048576 as `out_r2`,
                sum(traf_flows_1d.in_f)/1048576 as `in_f`,
                sum(traf_flows_1d.out_f)/1048576 as `out_f`
            FROM
            (
                SELECT
                    t1.actual_from,
                    t1.actual_to,
                    IF(
                        cidr LIKE '%/%',
                        INET_NTOA(INET_ATON( SUBSTRING_INDEX(cidr, '/', 1)) & 0xffffffff ^ ((0x1 << ( 32 - SUBSTRING_INDEX(cidr, '/', -1))  ) -1 )),
                        cidr
                    ) AS ip_min,
                    IF(
                        cidr LIKE '%/%',
                        INET_NTOA(INET_ATON( SUBSTRING_INDEX(cidr, '/', 1)) | ((0x100000000 >> SUBSTRING_INDEX(cidr, '/', -1) ) -1 )),
                        cidr
                    ) AS ip_max
                FROM
                (
                    SELECT
                        usage_ip_ports.actual_from,
                        usage_ip_ports.actual_to,
                        REPLACE(
                            TRIM(' ' FROM TRIM('	' FROM usage_ip_routes.net)),
                            '\\\\',
                            '/'
                        ) as cidr
                    FROM
                        usage_ip_ports,
                        usage_ip_routes
                    WHERE
                        usage_ip_ports.port_id = usage_ip_routes.port_id
                        AND usage_ip_ports.actual_to >= :date
                        AND LOCATE('.', usage_ip_routes.net) > 0
                        AND usage_ip_ports.id = :account_tariff_id
                ) t1
            ) t2,
                traf_flows_1d
                
            WHERE
                traf_flows_1d.ip_int BETWEEN INET_ATON(t2.ip_min) AND INET_ATON(t2.ip_max)
                AND traf_flows_1d.time BETWEEN t2.actual_from AND t2.actual_to
                AND traf_flows_1d.time >= :date
            
            GROUP BY
                DATE(traf_flows_1d.time)
SQL;
        $db = UsageIpPorts::getDb();
        $dataReader = $db->createCommand($sql, [
            ':date' => AccountTariff::getMinLogDatetime()->format('Y-m-d'),
            ':account_tariff_id' => $accountTariffId,
        ])
            ->query();
        foreach ($dataReader as $row) {
            $this->dateToValue[$row['date']] = $row;
        }

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