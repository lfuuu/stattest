<?php

namespace app\classes\important_events\events\properties;

use app\classes\Html;

class CurrentValueProperty extends UnknownProperty implements PropertyInterface
{

    const PROPERTY_CURRENT_VALUE = 'value';

    /**
     * @return array
     */
    public static function labels()
    {
        return [
            self::PROPERTY_CURRENT_VALUE => 'Значение на момент события',
        ];
    }

    /**
     * @return array
     */
    public function methods()
    {
        return [
            self::PROPERTY_CURRENT_VALUE => $this->getValue(),
        ];
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        $value = $this->setPropertyName('value')->getPropertyValue();

        return
            is_numeric($value)
                ? number_format($value, 2, '.', '')
                : $value;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return
            Html::tag('b', self::labels()[self::PROPERTY_CURRENT_VALUE] . ': ') . $this->getValue();
    }

}