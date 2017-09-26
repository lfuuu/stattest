<?php

namespace app\classes\behaviors\important_events;

use app\models\TroubleState;
use Yii;
use yii\base\Behavior;
use yii\base\ModelEvent;
use yii\db\ActiveRecord;
use app\models\important_events\ImportantEvents;
use app\models\important_events\ImportantEventsNames;
use app\models\important_events\ImportantEventsSources;
use app\models\Trouble;

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
            && !in_array($event->sender->state_id, TroubleState::$closedStates, true)
        ) {
            ImportantEvents::create(ImportantEventsNames::SET_STATE_TROUBLE,
                ImportantEventsSources::SOURCE_STAT, [
                    'trouble_id' => $trouble->id,
                    'stage_id' => $trouble->stage->stage_id,
                    'client_id' => $trouble->account->id,
                    'user_id' => Yii::$app->user->id,
                ]);
        }

        if ($trouble->stage->user_main != $event->sender->user_main) {
            ImportantEvents::create(ImportantEventsNames::SET_RESPONSIBLE_TROUBLE,
                ImportantEventsSources::SOURCE_STAT, [
                    'trouble_id' => $trouble->id,
                    'stage_id' => $trouble->stage->stage_id,
                    'client_id' => $trouble->account->id,
                    'user_id' => Yii::$app->user->id,
                ]);
        }

        if (!empty($trouble->stage->comment)) {
            ImportantEvents::create(ImportantEventsNames::NEW_COMMENT_TROUBLE,
                ImportantEventsSources::SOURCE_STAT, [
                    'trouble_id' => $trouble->id,
                    'stage_id' => $trouble->stage->stage_id,
                    'client_id' => $trouble->account->id,
                    'user_id' => Yii::$app->user->id,
                ]);
        }

        return true;
    }

}