<?php

namespace app\classes\important_events\events\properties\platform;

use app\classes\important_events\events\properties\CurrentValueProperty;

class PhoneNumberValueProperty extends CurrentValueProperty
{

    const PROPERTY_CURRENT_VALUE = 'phone_number';

    /**
     * @return array
     */
    public static function labels()
    {
        return [
            self::PROPERTY_CURRENT_VALUE => 'Номер телефона',
        ];
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->setPropertyName('phone_number')->getPropertyValue();
    }

}