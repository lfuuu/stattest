<?php

namespace app\classes\behaviors\important_events;

use Yii;
use yii\base\Behavior;
use yii\base\ModelEvent;
use yii\db\ActiveRecord;
use app\models\important_events\ImportantEvents;
use app\models\important_events\ImportantEventsNames;
use app\models\Trouble;

class TroubleStages extends Behavior
{

    const EVENT_SOURCE = 'stat';

    private $closedStates = [2,20,7,8,48];

    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'registerAddEvent',
        ];
    }

    /**
     * @param ModelEvent $event
     * @throws \app\exceptions\FormValidationException
     */
    public function registerAddEvent($event)
    {
        /** @var Trouble $trouble */
        $trouble = Trouble::findOne($event->sender->trouble_id);

        if ($trouble->stage->state_id != $event->sender->state_id && !in_array($event->sender->state_id, $this->closedStates, true)) {
            ImportantEvents::create(ImportantEventsNames::IMPORTANT_EVENT_SET_STATE_TROUBLE, self::EVENT_SOURCE, [
                'trouble_id' => $trouble->id,
                'stage_id' => $trouble->stage->stage_id,
                'client_id' => $trouble->account->id,
                'user_id' => Yii::$app->user->id,
            ]);
        }

        if ($trouble->stage->user_main != $event->sender->user_main) {
            ImportantEvents::create(ImportantEventsNames::IMPORTANT_EVENT_SET_RESPONSIBLE_TROUBLE, self::EVENT_SOURCE, [
                'trouble_id' => $trouble->id,
                'stage_id' => $trouble->stage->stage_id,
                'client_id' => $trouble->account->id,
                'user_id' => Yii::$app->user->id,
            ]);
        }

        if (!empty($trouble->stage->comment)) {
            ImportantEvents::create(ImportantEventsNames::IMPORTANT_EVENT_NEW_COMMENT_TROUBLE, self::EVENT_SOURCE, [
                'trouble_id' => $trouble->id,
                'stage_id' => $trouble->stage->stage_id,
                'client_id' => $trouble->account->id,
                'user_id' => Yii::$app->user->id,
            ]);
        }
    }

}