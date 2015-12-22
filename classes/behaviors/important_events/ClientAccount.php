<?php

namespace app\classes\behaviors\important_events;

use Yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use app\models\important_events\ImportantEvents;

class ClientAccount extends Behavior
{

    const EVENT_SOURCE = 'stat';

    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'ClientAccountAddEvent',
            ActiveRecord::EVENT_AFTER_UPDATE => 'ClientAccountUpdateEvent',
        ];
    }

    public function ClientAccountAddEvent($event)
    {
        ImportantEvents::create('new_account', self::EVENT_SOURCE, [
            'client_id' => $event->sender->id,
            'user_id' => Yii::$app->user->id,
        ]);
    }

    public function ClientAccountUpdateEvent($event)
    {
        $changed = array_diff_assoc($event->changedAttributes, $event->sender->attributes);
        if (count($changed)) {
            ImportantEvents::create('account_changed', self::EVENT_SOURCE, [
                'client_id' => $event->sender->id,
                'user_id' => Yii::$app->user->id,
                'changed' => implode(', ' , array_keys($changed)),
            ]);
        }
    }

}