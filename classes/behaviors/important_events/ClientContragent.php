<?php

namespace app\classes\behaviors\important_events;

use Yii;
use yii\base\Behavior;
use app\classes\Form;
use app\models\important_events\ImportantEvents;
use app\models\important_events\ImportantEventsNames;
use app\models\important_events\ImportantEventsSources;

class ClientContragent extends Behavior
{

    public function events()
    {
        return [
            Form::EVENT_AFTER_SAVE => 'registerTransferEvent',
        ];
    }

    /**
     * @param $event
     * @throws \app\exceptions\FormValidationException
     */
    public function registerTransferEvent($event)
    {
        ImportantEvents::create(ImportantEventsNames::IMPORTANT_EVENT_TRANSFER_CONTRAGENT, ImportantEventsSources::IMPORTANT_EVENT_SOURCE_STAT, [
            'contragent_id' => $event->sender->sourceClientAccount,
            'to_super_id' => $event->sender->targetClientAccount,
            'user_id' => Yii::$app->user->id,
        ]);
    }

}