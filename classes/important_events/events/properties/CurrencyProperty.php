<?php

namespace app\classes\important_events\events\properties;

use app\classes\Html;

/**
 * @property string $name
 */
class CurrencyProperty extends UnknownProperty implements PropertyInterface
{

    const PROPERTY_CURRENCY = 'currency';

    /**
     * @return []
     */
    public static function labels()
    {
        return [
            self::PROPERTY_CURRENCY => 'Валюта',
        ];
    }

    /**
     * @return []
     */
    public function methods()
    {
        return [
            self::PROPERTY_CURRENCY => $this->getValue(),
        ];
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->setPropertyName('currency')->getPropertyValue();
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return
            Html::tag('b', self::labels()[self::PROPERTY_CURRENCY] . ': ') . $this->getValue();
    }

}