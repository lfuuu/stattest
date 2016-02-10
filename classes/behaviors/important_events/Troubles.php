<?php

namespace app\classes\behaviors\important_events;

use Yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use app\models\important_events\ImportantEvents;

class Troubles extends Behavior
{

    const EVENT_SOURCE = 'stat';

    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'registerAddEvent',
            ActiveRecord::EVENT_AFTER_UPDATE => 'registerUpdateEvent',
        ];
    }

    public function registerAddEvent($event)
    {
        if ($event->sender->trouble_type == 'trouble' || $event->sender->trouble_type == 'task') {
            ImportantEvents::create('created_trouble', self::EVENT_SOURCE, [
                'trouble_id' => $event->sender->id,
                'client_id' => $event->sender->account->id,
                'user_id' => Yii::$app->user->id,
            ]);
        }
    }

    public function registerUpdateEvent($event)
    {
        if ($event->sender->date_close) {
            ImportantEvents::create('closed_trouble', self::EVENT_SOURCE, [
                'trouble_id' => $event->sender->id,
                'client_id' => $event->sender->account->id,
                'user_id' => Yii::$app->user->id,
            ]);
        }
    }

}