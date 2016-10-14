<?php

namespace app\classes\important_events\events\properties;

use app\classes\Html;

class LimitProperty extends UnknownProperty implements PropertyInterface
{

    const PROPERTY_LIMIT = 'limit';

    /**
     * @return array
     */
    public static function labels()
    {
        return [
            self::PROPERTY_LIMIT => 'Установленный лимит',
        ];
    }

    /**
     * @return array
     */
    public function methods()
    {
        return [
            self::PROPERTY_LIMIT => $this->getValue(),
        ];
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->setPropertyName('limit')->getPropertyValue();
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return
            Html::tag('b', self::labels()[self::PROPERTY_LIMIT] . ': ') . $this->getValue();
    }

}