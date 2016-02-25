<?php

namespace app\classes\behaviors\important_events;

use Yii;
use yii\base\Behavior;
use yii\base\ModelEvent;
use yii\db\ActiveRecord;
use app\models\important_events\ImportantEvents;
use app\models\important_events\ImportantEventsNames;

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

    /**
     * @param ModelEvent $event
     * @throws \app\exceptions\FormValidationException
     */
    public function ClientAccountAddEvent($event)
    {
        ImportantEvents::create(ImportantEventsNames::IMPORTANT_EVENT_NEW_ACCOUNT, self::EVENT_SOURCE, [
            'client_id' => $event->sender->id,
            'user_id' => Yii::$app->user->id,
        ]);
    }

    /**
     * @param ModelEvent $event
     * @throws \app\exceptions\FormValidationException
     */
    public function ClientAccountUpdateEvent($event)
    {
        $changed = array_diff_assoc($event->changedAttributes, $event->sender->attributes);
        if (count($changed)) {
            ImportantEvents::create(ImportantEventsNames::IMPORTANT_EVENT_ACCOUNT_CHANGED, self::EVENT_SOURCE, [
                'client_id' => $event->sender->id,
                'user_id' => Yii::$app->user->id,
                'changed' => implode(', ' , array_keys($changed)),
            ]);
        }
    }

}