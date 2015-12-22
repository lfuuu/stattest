<?php

namespace app\classes\behaviors\important_events;

use Yii;
use yii\base\Behavior;
use app\classes\Form;
use app\models\important_events\ImportantEvents;

class ClientContragent extends Behavior
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
        ImportantEvents::create('contragent_transfer', self::EVENT_SOURCE, [
            'contragent_id' => $event->sender->sourceClientAccount,
            'to_super_id' => $event->sender->targetClientAccount,
            'user_id' => Yii::$app->user->id,
        ]);
    }

}