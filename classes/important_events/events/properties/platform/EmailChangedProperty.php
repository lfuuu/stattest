<?php

namespace app\classes\important_events\events\properties\platform;

use app\classes\Html;
use app\classes\important_events\events\properties\PropertyInterface;
use app\classes\important_events\events\properties\UnknownProperty;

class EmailChangedProperty extends UnknownProperty implements PropertyInterface
{

    const PROPERTY_EMAIL_BEFORE = 'email.before';
    const PROPERTY_EMAIL_AFTER = 'email.after';

    /**
     * @return array
     */
    public static function labels()
    {
        return [
            self::PROPERTY_EMAIL_BEFORE => 'E-mail до изменения',
            self::PROPERTY_EMAIL_AFTER => 'E-mail после изменения',
        ];
    }

    /**
     * @return array
     */
    public function methods()
    {
        return [
            self::PROPERTY_EMAIL_BEFORE => $this->getValueBefore(),
            self::PROPERTY_EMAIL_AFTER => $this->getValueAfter(),
        ];
    }

    /**
     * @return string
     */
    public function getValueBefore()
    {
        return $this->setPropertyName('old_email')->getPropertyValue();
    }

    /**
     * @return string
     */
    public function getValueAfter()
    {
        return $this->setPropertyName('new_email')->getPropertyValue();
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return
            Html::tag('b', self::labels()[self::PROPERTY_EMAIL_BEFORE] . ': ') . $this->getValueBefore() .
            Html::tag('br') .
            Html::tag('b', self::labels()[self::PROPERTY_EMAIL_AFTER] . ': ') . $this->getValueAfter();
    }

}