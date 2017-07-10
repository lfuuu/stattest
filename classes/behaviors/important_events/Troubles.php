<?php

namespace app\classes\behaviors\important_events;

use Yii;
use yii\base\Behavior;
use yii\base\ModelEvent;
use yii\db\ActiveRecord;
use app\models\important_events\ImportantEventsNames;
use app\models\important_events\ImportantEvents;
use app\models\important_events\ImportantEventsSources;
use app\models\Trouble;
use app\models\TroubleStage;

class Troubles extends Behavior
{

    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'registerAddEvent',
            ActiveRecord::EVENT_AFTER_UPDATE => 'registerUpdateEvent',
        ];
    }

    /**
     * @param ModelEvent $event
     * @throws \app\exceptions\ModelValidationException
     */
    public function registerAddEvent($event)
    {
        if ($event->sender->trouble_type == 'trouble' || $event->sender->trouble_type == 'task') {
            ImportantEvents::create(ImportantEventsNames::CREATED_TROUBLE,
                ImportantEventsSources::SOURCE_STAT, [
                    'trouble_id' => $event->sender->id,
                    'client_id' => $event->sender->account->id,
                    'user_id' => Yii::$app->user->id,
                ]);
        }
    }

    /**
     * @param ModelEvent $event
     * @throws \app\exceptions\ModelValidationException
     */
    public function registerUpdateEvent($event)
    {
        /** @var Trouble $trouble */
        $trouble = Trouble::findOne($event->sender->id);

        if (
            $event->sender->date_close
                &&
            $trouble->currentStage->state_id != $event->sender->currentStage->state_id
                &&
            !in_array($event->sender->currentStage->state_id, TroubleStage::$closedStates, true)
        ) {
            ImportantEvents::create(ImportantEventsNames::CLOSED_TROUBLE,
                ImportantEventsSources::SOURCE_STAT, [
                    'trouble_id' => $event->sender->id,
                    'client_id' => $event->sender->account->id,
                    'user_id' => Yii::$app->user->id,
                ]);
        }
    }

}