<?php

namespace app\classes\behaviors\important_events;

use Yii;
use yii\base\Behavior;
use yii\base\ModelEvent;
use yii\db\ActiveRecord;
use app\models\important_events\ImportantEventsNames;
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

    /**
     * @param ModelEvent $event
     * @throws \app\exceptions\FormValidationException
     */
    public function registerAddEvent($event)
    {
        if ($event->sender->trouble_type == 'trouble' || $event->sender->trouble_type == 'task') {
            ImportantEvents::create(ImportantEventsNames::IMPORTANT_EVENT_CREATED_TROUBLE, self::EVENT_SOURCE, [
                'trouble_id' => $event->sender->id,
                'client_id' => $event->sender->account->id,
                'user_id' => Yii::$app->user->id,
            ]);
        }
    }

    /**
     * @param ModelEvent $event
     * @throws \app\exceptions\FormValidationException
     */
    public function registerUpdateEvent($event)
    {
        if ($event->sender->date_close) {
            ImportantEvents::create(ImportantEventsNames::IMPORTANT_EVENT_CLOSED_TROUBLE, self::EVENT_SOURCE, [
                'trouble_id' => $event->sender->id,
                'client_id' => $event->sender->account->id,
                'user_id' => Yii::$app->user->id,
            ]);
        }
    }

}