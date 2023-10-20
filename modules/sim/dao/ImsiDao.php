<?php

namespace app\modules\sim\dao;

use app\classes\Singleton;
use app\models\EventQueue;
use app\modules\sim\models\Imsi;
use app\modules\sim\models\ImsiExternalStatusLog;

class ImsiDao extends Singleton
{
    public function getSubscriberStatus($imsi, $isSaveResultToLog = true, $isSilentWhenSaving = true)
    {
        $this->_checkImsi($imsi);

        $event = EventQueue::go(EventQueue::SYNC_TELE2_GET_STATUS, ['imsi' => $imsi]);

        $result = $this->_waitEvent($event);

        $json = json_decode($result, true);

        if (!$json) {
            throw new \BadMethodCallException('answer error: ' . var_export($result, true));
        }

        $isConnected = $json['isConnected'];
        if (isset($json["GetResponse"]["MOAttributes"]["nsGetSubscriberData"]["nsSubscriberData"])) {
            $json = [$json["GetResponse"]["MOAttributes"]["nsGetSubscriberData"]["nsSubscriberData"] + ['isConnected' => $isConnected]];
        }

        if ($isSaveResultToLog) {
            try {
                ImsiExternalStatusLog::makeLog($imsi, ['result' => $json, 'status' => 'OK']);
            } catch (\Exception $e) {
                \Yii::error($e);
                if (!$isSilentWhenSaving) {
                    throw $e;
                }
            }
        }

        return $json;
    }

    private function _waitEvent(EventQueue $event)
    {
        $usleep = 200000; // 0.2 sec
        $count = 0;
        $result = false;
        do {
            usleep($usleep);
            $event->refresh();

            if ($event->status == EventQueue::STATUS_OK && $event->trace) {
                $result = $event->trace;
                break;
            }

        } while ($count++ < 120 && !$result);

        if (!$result) {
            throw new \RuntimeException('Timeout', 502);
        }

        return $result;
    }

    private function _checkImsi($imsi)
    {
        if (!$imsi || !preg_match('/^25\d{13}/', $imsi)) {
            throw new \InvalidArgumentException('bad IMSI');
        }
    }

    public function getImsiesForGetStatus($limit = 100)
    {
        return Imsi::getDb()->createCommand(<<<SQL
with l as (
    select imsi, max(insert_dt) as insert_dt
    from billing_uu.sim_imsi_external_status_log l
    group by imsi
    order by insert_dt
)
select i.imsi from billing_uu.sim_imsi i
left join l using (imsi)
where i.imsi between 250370000000000 and 250380000000000
order by coalesce(l.insert_dt, '2000-01-01 00:00:00+00:00':: timestamp), i.imsi
limit {$limit}
SQL
        )->queryColumn();
    }

}