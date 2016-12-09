<?php

namespace app\classes\important_events\events\properties\virtpbx;

use app\classes\important_events\events\properties\CurrentValueProperty;

class PasswordProperty extends CurrentValueProperty
{

    const PROPERTY_CURRENT_VALUE = 'password';

    /**
     * @return array
     */
    public static function labels()
    {
        return [
            self::PROPERTY_CURRENT_VALUE => 'Пароль',
        ];
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->setPropertyName('password')->getPropertyValue();
    }


}