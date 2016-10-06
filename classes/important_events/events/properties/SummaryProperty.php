<?php

namespace app\classes\important_events\events\properties;

use app\classes\Html;

/**
 * @property string $name
 */
class SummaryProperty extends UnknownProperty implements PropertyInterface
{

    const PROPERTY_SUMMARY_VALUE = 'value';

    /**
     * @return []
     */
    public static function labels()
    {
        return [
            self::PROPERTY_SUMMARY_VALUE => 'Сумма',
        ];
    }

    /**
     * @return []
     */
    public function methods()
    {
        return [
            self::PROPERTY_SUMMARY_VALUE => $this->getValue(),
        ];
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        $value = $this->setPropertyName('sum')->getPropertyValue();

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
            Html::tag('b', self::labels()[self::PROPERTY_SUMMARY_VALUE] . ': ') . $this->getValue();
    }

}