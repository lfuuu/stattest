<?php

namespace app\commands\convert;

use app\helpers\DateTimeZoneHelper;
use app\models\important_events\ImportantEvents;
use DateTimeImmutable;
use yii\console\Controller;

class EventsController extends Controller
{
    /**
     * Заполняем поля remote_ip && login в important_events
     */
    public function actionSetRemoteIpAndLogin()
    {
        $row = ImportantEvents::getDb()
            ->createCommand('SELECT
  min(date) AS min_date,
  max(date) AS max_date,
  count(*)  AS cnt
FROM `important_events`')
            ->queryOne();

        if (!$row) {
            return true;
        }

        if ($row['cnt'] < 10000) {
            $from = (new DateTimeImmutable('2000-01-01 00:00:00'));
            $to = (new DateTimeImmutable('2050-01-01 00:00:00'));
            $isOneStep = true;
        } else {
            $from = (new DateTimeImmutable($row['min_date']));
            $to = (new DateTimeImmutable($row['max_date']))->modify('+1 month');
            $isOneStep = false;
        }

        if ($isOneStep) {
            $this->_update($from, $to);
        } else {
            $cycleStartDate = $from;
            $cycleEndDate = $cycleStartDate->modify('+1 month');

            while ($cycleEndDate <= $to) {
                $this->_update($cycleStartDate, $cycleEndDate);

                $cycleStartDate = $cycleEndDate;
                $cycleEndDate = $cycleStartDate->modify('+1 month');
            }
        }
    }

    private function _update(DateTimeImmutable $from, DateTimeImmutable $to)
    {
        $start = microtime(true);

        $fromStr = $from->format(DateTimeZoneHelper::DATETIME_FORMAT);
        $toStr = $to->format(DateTimeZoneHelper::DATETIME_FORMAT);

        echo PHP_EOL . 'from: ' . $fromStr . ', to: ' . $toStr;

        $sql = <<<SQL
UPDATE `important_events` i,
  (
    SELECT
      id,
      substring_index(substring(context, length(substring_index(context, 'login', 1)) + 9), '"', 1)           l_login,
      substring_index(substring(context, length(substring_index(context, 'REMOTE_ADDR', 1)) + 15), '"', 1) AS l_addr
    FROM `important_events`
    WHERE TRUE
          AND event = 'client_logged_in'
          AND context LIKE '%is_support":false%'
          AND context NOT LIKE '%REMOTE_ADDR":""%'
          and date between '{$fromStr}' and '{$toStr}'
  ) a

SET i.remote_ip = a.l_addr, i.login = a.l_login
WHERE i.id = a.id
SQL;

        ImportantEvents::getDb()->createCommand($sql)->execute();

        echo " " . round(microtime(true) - $start, 2);
    }

}