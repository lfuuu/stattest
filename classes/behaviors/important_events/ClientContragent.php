<?php

namespace app\classes\behaviors\important_events;

use app\classes\Form;
use app\models\important_events\ImportantEvents;
use app\models\important_events\ImportantEventsNames;
use app\models\important_events\ImportantEventsSources;
use app\models\User;
use Yii;
use yii\base\Behavior;

class ClientContragent extends Behavior
{

    /**
     * @return array
     */
    public function events()
    {
        return [
            Form::EVENT_AFTER_SAVE => 'registerTransferEvent',
        ];
    }

    /**
     * @param $event
     * @throws \app\exceptions\ModelValidationException
     * @throws \yii\db\Exception
     * @throws \Exception
     */
    public function registerTransferEvent($event)
    {
        ImportantEvents::create(ImportantEventsNames::TRANSFER_CONTRAGENT,
            ImportantEventsSources::SOURCE_STAT, [
                'contragent_id' => $event->sender->sourceClientAccount,
                'to_super_id' => $event->sender->targetClientAccount,
                'user_id' => Yii::$app->user->id ?: User::SYSTEM_USER,
            ]);
    }

}