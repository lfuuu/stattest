<?php

namespace app\classes\actions\message;

use yii\base\Component;
use app\models\message\Template as MessageTemplate;
use app\models\important_events\ImportantEvents;

abstract class SendAction extends Component
{

    /**
     * @return string
     */
    public function getCode()
    {
        return static::ACTION_CODE;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return static::ACTION_TITLE;
    }

    /**
     * @param MessageTemplate $template
     * @param array $data
     */
    public function run(MessageTemplate $template, ImportantEvents $event)
    {
        print static::className();
    }

}