<?php

namespace app\classes\behaviors\important_events;

use Yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use app\models\important_events\ImportantEvents;
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

    public function registerAddEvent($event)
    {
        /** @var Trouble $trouble */
        $trouble = Trouble::findOne($event->sender->trouble_id);

        if ($trouble->stage->state_id != $event->sender->state_id && !in_array($event->sender->state_id, $this->closedStates, true)) {
            ImportantEvents::create('set_state_trouble', self::EVENT_SOURCE, [
                'trouble_id' => $trouble->id,
                'stage_id' => $trouble->stage->stage_id,
                'client_id' => $trouble->account->id,
                'user_id' => Yii::$app->user->id,
            ]);
        }

        if ($trouble->stage->user_main != $event->sender->user_main) {
            ImportantEvents::create('set_responsible_trouble', self::EVENT_SOURCE, [
                'trouble_id' => $trouble->id,
                'stage_id' => $trouble->stage->stage_id,
                'client_id' => $trouble->account->id,
                'user_id' => Yii::$app->user->id,
            ]);
        }

        if (!empty($trouble->stage->comment)) {
            ImportantEvents::create('new_comment_trouble', self::EVENT_SOURCE, [
                'trouble_id' => $trouble->id,
                'stage_id' => $trouble->stage->stage_id,
                'client_id' => $trouble->account->id,
                'user_id' => Yii::$app->user->id,
            ]);
        }
    }

}