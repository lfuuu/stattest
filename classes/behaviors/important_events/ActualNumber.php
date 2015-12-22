<?php

namespace app\classes\behaviors\important_events;

use Yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use app\models\important_events\ImportantEvents;

class ActualNumber extends Behavior
{

    const EVENT_SOURCE = 'stat';

    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'ActualNumberAddEvent',
            ActiveRecord::EVENT_AFTER_UPDATE => 'ActualNumberUpdateEvent',
            ActiveRecord::EVENT_AFTER_DELETE => 'ActualNumberDeleteEvent',
        ];
    }

    public function ActualNumberAddEvent($event)
    {
        ImportantEvents::create('usage_voip_on', self::EVENT_SOURCE, [
            'client_id' => $event->sender->client_id,
            'number' => $event->sender->number,
            'user_id' => Yii::$app->user->id,
        ]);
    }

    public function ActualNumberDeleteEvent($event)
    {
        ImportantEvents::create('usage_voip_off', self::EVENT_SOURCE, [
            'client_id' => $event->sender->client_id,
            'number' => $event->sender->number,
            'user_id' => Yii::$app->user->id,
        ]);
    }

    public function ActualNumberUpdateEvent($event)
    {
        print_r($event);
    }

}