<?php

namespace app\classes\important_events\events\properties;

use Yii;
use yii\base\Component;
use app\classes\Html;
use app\models\important_events\ImportantEvents;

class UnknownProperty extends Component implements PropertyInterface
{

    const PROPERTY_UNKNOWN = 'unknown';

    private
        $event,
        $propertyName;

    /**
     * @param ImportantEvents $event
     */
    public function __construct(ImportantEvents $event)
    {
        parent::__construct();

        $this->event = $event;
    }

    /**
     * @return array
     */
    public static function labels()
    {
        return [
            self::PROPERTY_UNKNOWN => 'Неизвестное свойство',
        ];
    }

    /**
     * @return array
     */
    public function methods()
    {
        return [
            self::PROPERTY_UNKNOWN => $this->getValue(),
        ];
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setPropertyName($name)
    {
        $this->propertyName = $name;
        return $this;
    }

    /**
     * @return string
     */
    protected function getPropertyValue()
    {
        $properties = $this->event->properties;

        if (is_array($properties)) {
            return $properties[$this->propertyName] ?? '';
        }

        return isset($properties->{$this->propertyName}) ? $properties->{$this->propertyName} : '';
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->getPropertyValue();
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return Html::tag('b', self::labels()[self::PROPERTY_UNKNOWN] . ': ') . $this->getValue();
    }

}