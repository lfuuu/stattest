<?php

namespace app\classes\behaviors\important_events;

use Yii;
use yii\base\Behavior;
use yii\base\ModelEvent;
use yii\db\ActiveRecord;
use app\models\important_events\ImportantEvents;
use app\models\important_events\ImportantEventsNames;
use app\models\important_events\ImportantEventsSources;
use app\models\Trouble;
use app\models\TroubleStage;

class TroubleStages extends Behavior
{

    /**
     * @return array
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'registerAddEvent',
        ];
    }

    /**
     * @param ModelEvent $event
     * @return bool
     * @throws \app\exceptions\ModelValidationException
     */
    public function registerAddEvent($event)
    {
        /** @var Trouble $trouble */
        $trouble = Trouble::findOne($event->sender->trouble_id);

        if (is_null($trouble->stage)) {
            return false;
        }

        if (
            $trouble->stage->state_id != $event->sender->state_id
            && !in_array($event->sender->state_id, TroubleStage::$closedStates, true)
        ) {
            ImportantEvents::create(ImportantEventsNames::IMPORTANT_EVENT_SET_STATE_TROUBLE,
                ImportantEventsSources::IMPORTANT_EVENT_SOURCE_STAT, [
                    'trouble_id' => $trouble->id,
                    'stage_id' => $trouble->stage->stage_id,
                    'client_id' => $trouble->account->id,
                    'user_id' => Yii::$app->user->id,
                ]);
        }

        if ($trouble->stage->user_main != $event->sender->user_main) {
            ImportantEvents::create(ImportantEventsNames::IMPORTANT_EVENT_SET_RESPONSIBLE_TROUBLE,
                ImportantEventsSources::IMPORTANT_EVENT_SOURCE_STAT, [
                    'trouble_id' => $trouble->id,
                    'stage_id' => $trouble->stage->stage_id,
                    'client_id' => $trouble->account->id,
                    'user_id' => Yii::$app->user->id,
                ]);
        }

        if (!empty($trouble->stage->comment)) {
            ImportantEvents::create(ImportantEventsNames::IMPORTANT_EVENT_NEW_COMMENT_TROUBLE,
                ImportantEventsSources::IMPORTANT_EVENT_SOURCE_STAT, [
                    'trouble_id' => $trouble->id,
                    'stage_id' => $trouble->stage->stage_id,
                    'client_id' => $trouble->account->id,
                    'user_id' => Yii::$app->user->id,
                ]);
        }

        return true;
    }

}