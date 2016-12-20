<?php

namespace app\classes\important_events\events\properties\platform;

use app\classes\important_events\events\properties\CurrentValueProperty;

class AttachedValueProperty extends CurrentValueProperty
{

    const PROPERTY_CURRENT_VALUE = 'attached';

    /**
     * @return array
     */
    public static function labels()
    {
        return [
            self::PROPERTY_CURRENT_VALUE => 'Подключение SIP-учетки',
        ];
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->setPropertyName('attached')->getPropertyValue();
    }


}