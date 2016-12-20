<?php

namespace app\classes\important_events\events\properties\platform;

use app\classes\important_events\events\properties\CurrentValueProperty;

class SipNumberValueProperty extends CurrentValueProperty
{

    const PROPERTY_CURRENT_VALUE = 'sip_number';

    /**
     * @return array
     */
    public static function labels()
    {
        return [
            self::PROPERTY_CURRENT_VALUE => 'Номер SIP-учетки',
        ];
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->setPropertyName('sip_number')->getPropertyValue();
    }

}