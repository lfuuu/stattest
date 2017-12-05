<?php

namespace app\classes\behaviors;

use app\models\EventQueue;
use app\models\UsageVirtpbx;
use yii\base\Behavior;
use yii\db\ActiveRecord;


class UpdateTask extends Behavior
{
    public $model = null;

    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => "makeTask",
            ActiveRecord::EVENT_AFTER_UPDATE => "makeTask",
            ActiveRecord::EVENT_AFTER_DELETE => "makeTask",
        ];
    }

    /**
     * Генерирует событие изменения модели
     *
     * @param \yii\db\AfterSaveEvent $event
     */
    public function makeTask($event)
    {
        if (!$event->changedAttributes) {
            return;
        }

        $isRealChanged = false;
        foreach ($event->changedAttributes as $attr => $value) {
            if ($event->sender->{$attr} != $value) {
                $isRealChanged = true;
                break;
            }
        }

        if (!$isRealChanged) {
            return;
        }

        if ($this->model == UsageVirtpbx::tableName()) {
            EventQueue::go(EventQueue::CHECK__VIRTPBX3, [
                'client_id' => $event->sender->clientAccount->id,
                'usage_id' => $event->sender->id,
            ]);
        }
    }
}
