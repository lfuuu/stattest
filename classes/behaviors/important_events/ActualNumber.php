<?php

namespace app\classes\behaviors\important_events;

use Yii;
use yii\base\Behavior;
use yii\base\ModelEvent;
use yii\db\ActiveRecord;
use app\models\important_events\ImportantEvents;
use app\models\important_events\ImportantEventsNames;
use app\models\important_events\ImportantEventsSources;

class ActualNumber extends Behavior
{

    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'ActualNumberAddEvent',
            ActiveRecord::EVENT_AFTER_UPDATE => 'ActualNumberUpdateEvent',
            ActiveRecord::EVENT_AFTER_DELETE => 'ActualNumberDeleteEvent',
        ];
    }

    /**
     * @param ModelEvent $event
     * @throws \app\exceptions\FormValidationException
     */
    public function ActualNumberAddEvent($event)
    {
        ImportantEvents::create(ImportantEventsNames::IMPORTANT_EVENT_ENABLED_USAGE, ImportantEventsSources::IMPORTANT_EVENT_SOURCE_STAT, [
            'client_id' => $event->sender->client_id,
            'usage' => 'usage_voip',
            'number' => $event->sender->number,
            'user_id' => Yii::$app->user->id,
        ]);
    }

    /**
     * @param ModelEvent $event
     * @throws \app\exceptions\FormValidationException
     */
    public function ActualNumberUpdateEvent($event)
    {
        ImportantEvents::create(ImportantEventsNames::IMPORTANT_EVENT_UPDATED_USAGE, ImportantEventsSources::IMPORTANT_EVENT_SOURCE_STAT, [
            'client_id' => $event->sender->client_id,
            'usage' => 'usage_voip',
            'number' => $event->sender->number,
            'user_id' => Yii::$app->user->id,
        ]);
    }

    /**
     * @param ModelEvent $event
     * @throws \app\exceptions\FormValidationException
     */
    public function ActualNumberDeleteEvent($event)
    {
        ImportantEvents::create(ImportantEventsNames::IMPORTANT_EVENT_DISABLED_USAGE, ImportantEventsSources::IMPORTANT_EVENT_SOURCE_STAT, [
            'client_id' => $event->sender->client_id,
            'usage' => 'usage_voip',
            'number' => $event->sender->number,
            'user_id' => Yii::$app->user->id,
        ]);
    }

}