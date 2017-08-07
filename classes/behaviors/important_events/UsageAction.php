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

    /**
     * @return array
     */
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
     * @throws \app\exceptions\ModelValidationException
     * @throws \yii\db\Exception
     * @throws \Exception
     */
    public function UsageAfterInsert($event)
    {
        ImportantEvents::create(ImportantEventsNames::CREATED_USAGE,
            ImportantEventsSources::SOURCE_STAT, [
                'client_id' => $event->sender->clientAccount->id,
                'usage' => $event->sender->tableName(),
                'usage_id' => $event->sender->id,
                'user_id' => Yii::$app->user->id,
            ]);
    }

    /**
     * @param ModelEvent $event
     * @throws \app\exceptions\ModelValidationException
     * @throws \yii\db\Exception
     * @throws \Exception
     */
    public function UsageAfterUpdate($event)
    {
        $changed = array_diff_assoc($event->changedAttributes, $event->sender->attributes);
        $changedCount = count($changed);

        if ($changedCount) {
            ImportantEvents::create(ImportantEventsNames::UPDATED_USAGE,
                ImportantEventsSources::SOURCE_STAT, [
                    'client_id' => $event->sender->clientAccount->id,
                    'usage' => $event->sender->tableName(),
                    'usage_id' => $event->sender->id,
                    'user_id' => Yii::$app->user->id,
                ]);
        }
    }

    /**
     * @param ModelEvent $event
     * @throws \app\exceptions\ModelValidationException
     * @throws \yii\db\Exception
     * @throws \Exception
     */
    public function UsageAfterDelete($event)
    {
        ImportantEvents::create(ImportantEventsNames::DELETED_USAGE,
            ImportantEventsSources::SOURCE_STAT, [
                'client_id' => $event->sender->clientAccount->id,
                'usage' => $event->sender->tableName(),
                'usage_id' => $event->sender->id,
                'user_id' => Yii::$app->user->id,
            ]);
    }

    /**
     * @param ModelEvent $event
     * @throws \app\exceptions\ModelValidationException
     * @throws \yii\db\Exception
     * @throws \Exception
     */
    public function UsageTransferEvent($event)
    {
        ImportantEvents::create(ImportantEventsNames::TRANSFER_USAGE,
            ImportantEventsSources::SOURCE_STAT, [
                'client_id' => $event->sender->service->clientAccount->id,
                'usage' => $event->sender->service->tableName(),
                'usage_id' => $event->sender->service->id,
                'user_id' => Yii::$app->user->id,
            ]);
    }

}