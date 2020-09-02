<?php

namespace app\health;

use app\helpers\DateTimeZoneHelper;
use app\modules\uu\models\Period;
use app\modules\uu\models\ServiceType;
use app\modules\uu\models\Tariff;

class MonitorDoublePayment extends Monitor
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
SELECT
  client_id,
  count(*)    cnt,
  sum(sum)/count(*) AS sm
FROM `newpayments`
WHERE payment_date >= :date AND comment LIKE '%Sberbank %'
GROUP BY comment
HAVING cnt > 1
ORDER BY cnt DESC
SQL;

        $count = 0;
        foreach (\Yii::$app->db->createCommand($sql, [':date' => $date])->queryAll() as $row) {
            $message .= $row['client_id'] . ' (' . round($row['sm'], 2) . '), ';
            $count++;
        }

        $this->_message = rtrim($message, ', ');
        return $count;
    }
}