<?php

namespace app\classes\behaviors\important_events;

use Yii;
use yii\base\Behavior;
use app\classes\Form;
use app\models\important_events\ImportantEvents;

class UsageTransfer extends Behavior
{

    const EVENT_SOURCE = 'stat';

    public function events()
    {
        return [
            Form::EVENT_AFTER_SAVE => 'registerTransferEvent',
        ];
    }

    public function registerTransferEvent($event)
    {
        ImportantEvents::create('usage_transfer', self::EVENT_SOURCE, [
            'client_id' => $event->sender->service->clientAccount->id,
            'usage_id' => $event->sender->service->id,
            'user_id' => Yii::$app->user->id,
        ]);
    }

}