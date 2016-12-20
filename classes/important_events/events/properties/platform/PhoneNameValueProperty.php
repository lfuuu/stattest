<?php

namespace app\classes\important_events\events\properties\platform;

use app\classes\important_events\events\properties\CurrentValueProperty;

class PhoneNameValueProperty extends CurrentValueProperty
{

    const PROPERTY_CURRENT_VALUE = 'phone_name';

    /**
     * @return array
     */
    public static function labels()
    {
        return [
            self::PROPERTY_CURRENT_VALUE => 'Имя абонента на ВАТС',
        ];
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->setPropertyName('phone_name')->getPropertyValue();
    }

}