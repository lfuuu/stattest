<?php

namespace app\classes\important_events\events\properties\platform;

use app\classes\important_events\events\properties\CurrentValueProperty;

class NewIpValueProperty extends CurrentValueProperty
{

    const PROPERTY_CURRENT_VALUE = 'new_ip';

    /**
     * @return array
     */
    public static function labels()
    {
        return [
            self::PROPERTY_CURRENT_VALUE => 'Новый IP',
        ];
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->setPropertyName('new_ip')->getPropertyValue();
    }

}