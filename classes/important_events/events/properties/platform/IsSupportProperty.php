<?php

namespace app\classes\important_events\events\properties\platform;

use app\classes\important_events\events\properties\CurrentValueProperty;

class IsSupportProperty extends CurrentValueProperty
{

    const PROPERTY_CURRENT_VALUE = 'is_support';

    /**
     * @return array
     */
    public static function labels()
    {
        return [
            self::PROPERTY_CURRENT_VALUE => 'Сделано c помощью тех. поддержки',
        ];
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->setPropertyName('is_support')->getPropertyValue() ? 'Да' : 'Нет';
    }


}