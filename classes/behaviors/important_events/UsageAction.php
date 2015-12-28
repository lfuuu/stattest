<?php

namespace app\classes\behaviors\important_events;

use Yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use app\classes\Form;
use app\models\important_events\ImportantEvents;

class UsageAction extends Behavior
{

    const EVENT_SOURCE = 'stat';

    public function events()
    {
        return [
            Form::EVENT_AFTER_SAVE => 'UsageTransferEvent',
            ActiveRecord::EVENT_AFTER_INSERT => 'UsageAfterInsert',
            ActiveRecord::EVENT_AFTER_UPDATE => 'UsageAfterUpdate',
            ActiveRecord::EVENT_AFTER_DELETE => 'UsageAfterDelete',
        ];
    }

    public function UsageAfterInsert($event)
    {
        ImportantEvents::create('created_usage', self::EVENT_SOURCE, [
            'client_id' => $event->sender->clientAccount->id,
            'usage' =>  $event->sender->tableName(),
            'usage_id' => $event->sender->id,
            'user_id' => Yii::$app->user->id,
        ]);
    }

    public function UsageAfterUpdate($event)
    {
        $changed = array_diff_assoc($event->changedAttributes, $event->sender->attributes);
        $changedCount = count($changed);

        if ($changedCount) {
            ImportantEvents::create('updated_usage', self::EVENT_SOURCE, [
                'client_id' => $event->sender->clientAccount->id,
                'usage' => $event->sender->tableName(),
                'usage_id' => $event->sender->id,
                'user_id' => Yii::$app->user->id,
                'changed' => implode(', ', array_keys($changed)),
            ]);
        }
    }

    public function UsageAfterDelete($event)
    {
        ImportantEvents::create('deleted_usage', self::EVENT_SOURCE, [
            'client_id' => $event->sender->clientAccount->id,
            'usage' => $event->sender->tableName(),
            'usage_id' => $event->sender->id,
            'user_id' => Yii::$app->user->id,
        ]);
    }

    public function UsageTransferEvent($event)
    {
        ImportantEvents::create('transfer_usage', self::EVENT_SOURCE, [
            'client_id' => $event->sender->service->clientAccount->id,
            'usage' => $event->sender->service->tableName(),
            'usage_id' => $event->sender->service->id,
            'user_id' => Yii::$app->user->id,
        ]);
    }

}