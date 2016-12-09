<?php

namespace app\classes\important_events\events\properties\virtpbx;

use app\classes\important_events\events\properties\CurrentValueProperty;

class LoginProperty extends CurrentValueProperty
{

    const PROPERTY_CURRENT_VALUE = 'login';

    /**
     * @return array
     */
    public static function labels()
    {
        return [
            self::PROPERTY_CURRENT_VALUE => 'Логин',
        ];
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->setPropertyName('login')->getPropertyValue();
    }


}