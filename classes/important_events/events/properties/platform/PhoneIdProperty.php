<?php

namespace app\classes\important_events\events\properties\platform;

use app\classes\important_events\events\properties\CurrentValueProperty;

class PhoneIdProperty extends CurrentValueProperty
{

    const PROPERTY_CURRENT_VALUE = 'phone_id';

    /**
     * @return array
     */
    public static function labels()
    {
        return [
            self::PROPERTY_CURRENT_VALUE => 'ID номера',
        ];
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->setPropertyName('phone_id')->getPropertyValue();
    }

}