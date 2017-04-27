<?php

namespace app\classes\important_events\events\properties\platform;

use app\classes\important_events\events\properties\CurrentValueProperty;

class ConfirmUrlProperty extends CurrentValueProperty
{

    const PROPERTY_CURRENT_VALUE = 'link';

    /**
     * @return array
     */
    public static function labels()
    {
        return [
            self::PROPERTY_CURRENT_VALUE => 'Ссылка установки пароля',
        ];
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->setPropertyName('link')->getPropertyValue();
    }


}