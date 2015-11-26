<?php

namespace app\classes\actions\message;

use yii\base\Component;

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

}