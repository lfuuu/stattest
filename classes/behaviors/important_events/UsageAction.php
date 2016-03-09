<?php

namespace app\classes\behaviors\important_events;

use Yii;
use yii\base\Behavior;
use yii\base\ModelEvent;
use yii\db\ActiveRecord;
use app\classes\Form;
use app\models\important_events\ImportantEvents;
use app\models\important_events\ImportantEventsNames;
use app\models\important_events\ImportantEventsSources;

class UsageAction extends Behavior
{

    public function events()
    {
        return [
            Form::EVENT_AFTER_SAVE => 'UsageTransferEvent',
            ActiveRecord::EVENT_AFTER_INSERT => 'UsageAfterInsert',
            ActiveRecord::EVENT_AFTER_UPDATE => 'UsageAfterUpdate',
            ActiveRecord::EVENT_AFTER_DELETE => 'UsageAfterDelete',
        ];
    }

    /**
     * @param ModelEvent $event
     * @throws \app\exceptions\FormValidationException
     */
    public function UsageAfterInsert($event)
    {
        ImportantEvents::create(ImportantEventsNames::IMPORTANT_EVENT_CREATED_USAGE, ImportantEventsSources::IMPORTANT_EVENT_SOURCE_STAT, [
            'client_id' => $event->sender->clientAccount->id,
            'usage' =>  $event->sender->tableName(),
            'usage_id' => $event->sender->id,
            'user_id' => Yii::$app->user->id,
        ]);
    }

    /**
     * @param ModelEvent $event
     * @throws \app\exceptions\FormValidationException
     */
    public function UsageAfterUpdate($event)
    {
        $changed = array_diff_assoc($event->changedAttributes, $event->sender->attributes);
        $changedCount = count($changed);

        if ($changedCount) {
            ImportantEvents::create(ImportantEventsNames::IMPORTANT_EVENT_UPDATED_USAGE, ImportantEventsSources::IMPORTANT_EVENT_SOURCE_STAT, [
                'client_id' => $event->sender->clientAccount->id,
                'usage' => $event->sender->tableName(),
                'usage_id' => $event->sender->id,
                'user_id' => Yii::$app->user->id,
                'changed' => implode(', ', array_keys($changed)),
            ]);
        }
    }

    /**
     * @param ModelEvent $event
     * @throws \app\exceptions\FormValidationException
     */
    public function UsageAfterDelete($event)
    {
        ImportantEvents::create(ImportantEventsNames::IMPORTANT_EVENT_DELETED_USAGE, ImportantEventsSources::IMPORTANT_EVENT_SOURCE_STAT, [
            'client_id' => $event->sender->clientAccount->id,
            'usage' => $event->sender->tableName(),
            'usage_id' => $event->sender->id,
            'user_id' => Yii::$app->user->id,
        ]);
    }

    /**
     * @param ModelEvent $event
     * @throws \app\exceptions\FormValidationException
     */
    public function UsageTransferEvent($event)
    {
        ImportantEvents::create(ImportantEventsNames::IMPORTANT_EVENT_TRANSFER_USAGE, ImportantEventsSources::IMPORTANT_EVENT_SOURCE_STAT, [
            'client_id' => $event->sender->service->clientAccount->id,
            'usage' => $event->sender->service->tableName(),
            'usage_id' => $event->sender->service->id,
            'user_id' => Yii::$app->user->id,
        ]);
    }

}