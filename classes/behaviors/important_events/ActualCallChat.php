<?php

namespace app\classes\behaviors\important_events;

use Yii;
use yii\base\Behavior;
use yii\base\ModelEvent;
use yii\db\ActiveRecord;
use app\models\important_events\ImportantEvents;
use app\models\important_events\ImportantEventsNames;
use app\models\important_events\ImportantEventsSources;

class ActualCallChat extends Behavior
{
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'ActualClassChatAddEvent',
            ActiveRecord::EVENT_AFTER_UPDATE => 'ActualClassChatUpdateEvent',
            ActiveRecord::EVENT_AFTER_DELETE => 'ActualClassChatDeleteEvent',
        ];
    }

    /**
     * @param ModelEvent $event
     * @throws \app\exceptions\FormValidationException
     */
    public function ActualClassChatAddEvent($event)
    {
        ImportantEvents::create(ImportantEventsNames::IMPORTANT_EVENT_ENABLED_USAGE, ImportantEventsSources::IMPORTANT_EVENT_SOURCE_STAT, [
            'client_id' => $event->sender->client_id,
            'usage' => 'usage_call_chat',
            'usage_id' => $event->sender->usage_id,
            'user_id' => Yii::$app->user->id,
        ]);
    }

    /**
     * @param ModelEvent $event
     * @throws \app\exceptions\FormValidationException
     */
    public function ActualClassChatUpdateEvent($event)
    {
        ImportantEvents::create(ImportantEventsNames::IMPORTANT_EVENT_UPDATED_USAGE, ImportantEventsSources::IMPORTANT_EVENT_SOURCE_STAT, [
            'client_id' => $event->sender->client_id,
            'usage' => 'usage_call_chat',
            'usage_id' => $event->sender->usage_id,
            'user_id' => Yii::$app->user->id,
        ]);
    }

    /**
     * @param ModelEvent $event
     * @throws \app\exceptions\FormValidationException
     */
    public function ActualClassChatDeleteEvent($event)
    {
        ImportantEvents::create(ImportantEventsNames::IMPORTANT_EVENT_DISABLED_USAGE, ImportantEventsSources::IMPORTANT_EVENT_SOURCE_STAT, [
            'client_id' => $event->sender->client_id,
            'usage' => 'usage_call_chat',
            'usage_id' => $event->sender->usage_id,
            'user_id' => Yii::$app->user->id,
        ]);
    }

}