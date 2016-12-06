<?php

namespace app\classes\important_events\events\properties\virtpbx;

use app\classes\important_events\events\properties\CurrentValueProperty;
use yii\helpers\ArrayHelper;

class SipListProperty extends CurrentValueProperty
{

    const PROPERTY_CURRENT_VALUE = 'sip_list';

    /**
     * @return array
     */
    public static function labels()
    {
        return [
            self::PROPERTY_CURRENT_VALUE => 'Список SIP',
        ];
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        $listOfSIP = $this->setPropertyName('sipdevices')->getPropertyValue();
        $listOfValues = ArrayHelper::getColumn($listOfSIP, 'name');
        if (count($listOfValues)) {
            return implode(', ', $listOfValues);
        } else {
            return 'неизвестно';
        }
    }


}