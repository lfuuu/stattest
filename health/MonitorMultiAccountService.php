<?php

namespace app\health;

use app\helpers\DateTimeZoneHelper;
use app\modules\uu\models\Period;
use app\modules\uu\models\ServiceType;
use app\modules\uu\models\Tariff;

class MonitorMultiAccountService extends Monitor
{
    private $_message = '';

    /**
     * @inheritdoc
     * @return int[]
     */
    public function getLimits()
    {
        return [1, 1, 1];
    }

    /**
     * Получение сообщения для статуса
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->_message;
    }

    /**
     * Текущее значение
     *
     * @return int
     */
    public function getValue()
    {
        $message = '';

        $date = (new \DateTimeImmutable('now'))->modify('-1 month')->format(DateTimeZoneHelper::DATE_FORMAT);


        $sql = <<<SQL
select prev_account_tariff_id as uu_service_id, 
       count(distinct client_account_id) as cnt
from uu_account_tariff
where prev_account_tariff_id is not null
group by prev_account_tariff_id
having cnt > 1

SQL;

        $count = 0;
        foreach (\Yii::$app->db->createCommand($sql, [':date' => $date])->queryAll() as $row) {
            $message .= $row['uu_service_id']. ', ';
            $count++;
        }

        $this->_message = rtrim($message, ', ');
        return $count;
    }
}