<?php

namespace app\classes\important_events\events\properties\platform;

use app\classes\important_events\events\properties\CurrentValueProperty;

class CoreUserIdProperty extends CurrentValueProperty
{

    const PROPERTY_CURRENT_VALUE = 'core_user_id';

    /**
     * @return array
     */
    public static function labels()
    {
        return [
            self::PROPERTY_CURRENT_VALUE => 'ID абонента привязанного к пользователю ВАТС',
        ];
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->setPropertyName('core_user_id')->getPropertyValue();
    }

}