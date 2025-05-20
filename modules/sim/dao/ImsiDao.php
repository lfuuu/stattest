<?php

namespace app\modules\sim\dao;

use app\classes\Singleton;
use app\helpers\DateTimeZoneHelper;
use app\models\EventQueue;
use app\modules\sim\models\CardStatus;
use app\modules\sim\models\Imsi;
use app\modules\sim\models\ImsiExternalStatusLog;
use app\modules\sim\models\ImsiPartner;
use app\modules\sim\models\ImsiProfile;

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

    /**
     * @param integer $statusId
     * @return array|\yii\db\ActiveRecord
     */
    public function getNextImsi($statusId)
    {
        $transaction = CardStatus::getDb()->beginTransaction();
        try {
            $selectSql = CardStatus::find()->where(['id' => $statusId])->createCommand()->rawSql. ' FOR UPDATE';
            $status = CardStatus::getDb()->createCommand($selectSql)->queryOne();

            if (!$status) {
                throw new \InvalidArgumentException('Status not found');
            }

            $nextImsiQuery = $this->_getNextImsiQuery($statusId);

            if ($status['last_iccid']) {
                $nextImsiQuery->andWhere(['>', 'c.iccid', $status['last_iccid']]);
            }

            /** @var Imsi $nextImsi */
            $nextImsi = $nextImsiQuery->one();

            if (!$nextImsi) {
                $nextImsi = $this->_getNextImsiQuery($statusId)->one(); // если не нашли последнюю, начинаем с первой
            }

            if (!$nextImsi) {
                throw new \LogicException(sprintf('Не найдена IMSI для склада %s (id: %s)', $status['name'], $status['id']));
            }

            CardStatus::getDb()
                ->createCommand()
                ->update(
                    CardStatus::tableName(),
                    ['last_iccid' => $nextImsi->iccid, 'last_iccid_set_at' => DateTimeZoneHelper::getUtcDateTime()->format(DateTimeZoneHelper::DATETIME_FORMAT)],
                    ['id' => $statusId]
                )
                ->execute();

            $transaction->commit();

            return $nextImsi;
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    private function _getNextImsiQuery($statusId)
    {
        return Imsi::find()->alias('i')
            ->joinWith('card c')
            ->andWhere([
                'i.profile_id' => ImsiProfile::ID_MSN_RUS,
                'i.partner_id' => ImsiPartner::ID_TELE2,
                'c.status_id' => $statusId,
                'c.is_active' => 1,
                'i.is_active' => 1,
                'c.client_account_id' => null,
            ])
            ->orderBy(['c.iccid' => SORT_ASC])
            ;
    }


}