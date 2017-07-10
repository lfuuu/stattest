<?php
namespace app\classes\behaviors;

use app\models\ClientFlag;
use app\models\important_events\ImportantEvents;
use app\models\important_events\ImportantEventsNames;
use app\models\important_events\ImportantEventsSources;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\db\AfterSaveEvent;

class NotifiedFlagToImportantEvent extends Behavior
{
    /**
     * @return array
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'makeEvent',
            ActiveRecord::EVENT_AFTER_UPDATE => 'makeEvent',
        ];
    }

    /**
     * Генерация важного события на изменение флагов ЛС
     *
     * @param AfterSaveEvent $event
     */
    public function makeEvent(AfterSaveEvent $event)
    {
        if (!$event->changedAttributes) {
            return ;
        }

        /** @var ClientFlag $model */
        $model = $event->sender;

        if (isset($event->changedAttributes['is_notified_7day']) || ($model->is_notified_7day && $event->name == ActiveRecord::EVENT_AFTER_INSERT)) {
            $importantEvent = $model->is_notified_7day ?
                ImportantEventsNames::NOTIFIED_7DAYS :
                ImportantEventsNames::RESET_NOTIFIED_7DAYS;

            ImportantEvents::create($importantEvent,
                ImportantEventsSources::SOURCE_STAT,
                [
                    'client_id' => $model->account_id,
                ]
            );
            $model->isSetFlag = true;
        }

        if (isset($event->changedAttributes['is_notified_3day']) || ($model->is_notified_3day && $event->name == ActiveRecord::EVENT_AFTER_INSERT)) {
            $importantEvent = $model->is_notified_3day ?
                ImportantEventsNames::NOTIFIED_3DAYS :
                ImportantEventsNames::RESET_NOTIFIED_3DAYS;

            ImportantEvents::create($importantEvent,
                ImportantEventsSources::SOURCE_STAT,
                [
                    'client_id' => $model->account_id,
                ]
            );
            $model->isSetFlag = true;
        }

        if (isset($event->changedAttributes['is_notified_1day']) || ($model->is_notified_1day && $event->name == ActiveRecord::EVENT_AFTER_INSERT)) {
            $importantEvent = $model->is_notified_1day ?
                ImportantEventsNames::NOTIFIED_1DAYS :
                ImportantEventsNames::RESET_NOTIFIED_1DAYS;

            ImportantEvents::create($importantEvent,
                ImportantEventsSources::SOURCE_STAT,
                [
                    'client_id' => $model->account_id,
                ]
            );
            $model->isSetFlag = true;
        }
    }
}