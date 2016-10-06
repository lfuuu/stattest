<?php

namespace app\classes\important_events\events\properties;

use app\classes\Html;

/**
 * @property string $name
 */
class BeforeValueProperty extends UnknownProperty implements PropertyInterface
{

    const PROPERTY_BEFORE_VALUE = 'before';

    /**
     * @return []
     */
    public static function labels()
    {
        return [
            self::PROPERTY_BEFORE_VALUE => 'Значение до наступления события',
        ];
    }

    /**
     * @return []
     */
    public function methods()
    {
        return [
            self::PROPERTY_BEFORE_VALUE => $this->getValue(),
        ];
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        $value = $this->setPropertyName('before')->getPropertyValue();

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
            Html::tag('b', self::labels()[self::PROPERTY_BEFORE_VALUE] . ': ') . $this->getValue();
    }

}