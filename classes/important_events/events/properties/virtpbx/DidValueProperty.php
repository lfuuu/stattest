<?php

namespace app\classes\important_events\events\properties\virtpbx;

use app\classes\important_events\events\properties\CurrentValueProperty;

class DidValueProperty extends CurrentValueProperty
{

    const PROPERTY_CURRENT_VALUE = 'did';

    /**
     * @return array
     */
    public static function labels()
    {
        return [
            self::PROPERTY_CURRENT_VALUE => 'Номер',
        ];
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->setPropertyName('did')->getPropertyValue();
    }


}