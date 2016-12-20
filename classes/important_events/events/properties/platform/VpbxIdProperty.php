<?php

namespace app\classes\important_events\events\properties\platform;

use app\classes\important_events\events\properties\CurrentValueProperty;

class VpbxIdProperty extends CurrentValueProperty
{

    const PROPERTY_CURRENT_VALUE = 'vpbx_id';

    /**
     * @return array
     */
    public static function labels()
    {
        return [
            self::PROPERTY_CURRENT_VALUE => 'ID ВАТС',
        ];
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->setPropertyName('vpbx_id')->getPropertyValue();
    }


}