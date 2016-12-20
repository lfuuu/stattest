<?php

namespace app\classes\important_events\events\properties\platform;

use app\classes\Html;
use app\classes\important_events\events\properties\CurrentValueProperty;

class BodyValueProperty extends CurrentValueProperty
{

    const PROPERTY_CURRENT_VALUE = 'body';

    /**
     * @return array
     */
    public static function labels()
    {
        return [
            self::PROPERTY_CURRENT_VALUE => 'Содержание',
        ];
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return Html::tag('pre', $this->setPropertyName('body')->getPropertyValue());
    }

}