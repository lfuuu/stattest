<?php

namespace app\classes\important_events\events\properties;

use app\classes\Html;
use app\models\important_events\ImportantEvents;
use app\models\Trouble;
use app\models\TroubleStage;
use app\models\User;

class TroubleStageProperty extends UnknownProperty implements PropertyInterface
{

    /** @var TroubleStage|null $stage */
    private $stage = null;

    /**
     * @param ImportantEvents $event
     */
    public function __construct(ImportantEvents $event)
    {
        parent::__construct($event);

        $troubleStageId = $this->setPropertyName('stage_id')->getPropertyValue();
        $troubleId = $this->setPropertyName('trouble_id')->getPropertyValue();

        if (!$troubleStageId && $troubleId) {
            if (($trouble = Trouble::findOne(['id' => $troubleId])) !== null) {
                /** @var Trouble $trouble */
                $troubleStageId = $trouble->cur_stage_id;
            }
        }

        $stage = TroubleStage::findOne(['stage_id' => $troubleStageId]);
        if (!is_null($stage)) {
            $this->stage = $stage;
        }
    }

    /**
     * @return array
     */
    public static function labels()
    {
        return [];
    }

    /**
     * @return array
     */
    public function methods()
    {
        return [];
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return (!is_null($this->stage) ? $this->stage->stage_id : 0);
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        if (!$this->getValue()) {
            return '';
        }

        $description = Html::tag('b', 'Статус: ') . $this->stage->state->name;

        if (!empty($this->stage->comment)) {
            $description .=
                Html::tag('br') .
                Html::tag('b', 'Комментарий: ') . $this->stage->comment;
        }

        if (!empty($this->stage->user_main)) {
            /** @var User $user */
            $user = User::findOne(['user' => $this->stage->user_main]);
            $description .=
                Html::tag('br') .
                Html::tag('b', 'Ответственный: ') . $user->name;
        }

        return $description;
    }

}