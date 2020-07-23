<?php

namespace app\modules\sim\behaviors;

use app\classes\model\ActiveRecord;
use app\exceptions\ModelValidationException;
use app\models\EventQueue;
use app\models\Number;
use app\modules\sim\models\Card;
use app\modules\sim\models\Imsi;
use yii\base\Behavior;
use yii\base\Event;
use yii\db\AfterSaveEvent;

class ImsiTele2StatusBehavior extends Behavior
{
    /**
     * @return array
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterUpdate',
        ];
    }

    /**
     * При обновлении сим-карты синхронизировать номер, если надо
     *
     * @param AfterSaveEvent $event
     * @throws ModelValidationException
     */
    public function afterUpdate(AfterSaveEvent $event)
    {
        /** @var Imsi $model */
        $model = $event->sender;

        if (!array_key_exists('msisdn', $event->changedAttributes)) {
            return;
        }

        $oldMsisdn = $event->changedAttributes['msisdn'];
        $newMsisdn = $model->msisdn;

        if ($oldMsisdn == $newMsisdn) {
            return;
        }

        if ($oldMsisdn) {
            EventQueue::go(EventQueue::SYNC_TELE2_UNLINK_IMSI, [
                'voip_number' => $oldMsisdn,
                'imsi' => $model->imsi,
                'iccid' => $model->iccid,
            ]);
        }

        if ($newMsisdn) {
            EventQueue::go(EventQueue::SYNC_TELE2_LINK_IMSI, [
                'voip_number' => $newMsisdn,
                'imsi' => $model->imsi,
                'iccid' => $model->iccid,
            ]);
        }
    }
}