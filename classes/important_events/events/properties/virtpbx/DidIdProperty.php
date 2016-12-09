<?php

namespace app\classes\important_events\events\properties\virtpbx;

use app\classes\important_events\events\properties\CurrentValueProperty;

class DidIdProperty extends CurrentValueProperty
{

    const PROPERTY_CURRENT_VALUE = 'did_id';

    /**
     * @return array
     */
    public static function labels()
    {
        return [
            self::PROPERTY_CURRENT_VALUE => 'ID DID',
        ];
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->setPropertyName('did_id')->getPropertyValue();
    }

}