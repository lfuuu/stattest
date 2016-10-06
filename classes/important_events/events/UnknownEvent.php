<?php

namespace app\classes\important_events\events;

use app\classes\important_events\events\properties\UnknownProperty;
use yii\base\Component;
use yii\base\Exception;
use app\classes\Html;
use app\classes\important_events\events\properties\PropertyInterface;
use app\classes\important_events\events\properties\ClientProperty;
use app\classes\important_events\events\properties\DateProperty;
use app\classes\important_events\events\properties\IpProperty;
use app\models\important_events\ImportantEvents;

class UnknownEvent extends Component
{

    protected $eventModel = null;
    public static
        $properties = [
            'date' => DateProperty::class,
            'client' => ClientProperty::class,
            'ip' => IpProperty::class,
        ];

    /**
     * @param ImportantEvents|null $eventModel
     */
    public function __construct(ImportantEvents $eventModel = null)
    {
        parent::__construct();

        if (!is_null($eventModel)) {
            $this->eventModel = $eventModel;
        }
    }

    /**
     * @param $name
     * @return mixed
     * @throws Exception
     */
    public function getProperty($name)
    {
        if (is_null($this->eventModel)) {
            throw new Exception('Не указана модель данных');
        }

        // Получение базовой составляющей свойства
        list($property) = explode('.', $name, 2);

        // Список всех свойств
        $properties = self::getFullProperties();

        if (isset($properties[$property])) {
            $property = $properties[$property];

            if (class_exists($property)) {
                /** @var PropertyInterface $property */
                $property = new $property($this->eventModel);
                $methods = $property->methods();

                if (!array_key_exists($name, $methods)) {
                    throw new Exception('Не найдено свойство "' . $name . '"');
                }

                return $methods[$name];
            }
        }

        // Не был найден обработчик свойства, искать в модели
        return (new UnknownProperty($this->eventModel))->setPropertyName($name)->getValue();
    }

    /**
     * @return []
     */
    public function getProperties()
    {
        $result = [];

        foreach (self::getFullProperties() as $key => $property) {
            if (class_exists($property)) {
                /** @var PropertyInterface $property */
                $result = array_merge($result, (array)$property::labels());
            } else {
                $result[$key] = $property;
            }
        }

        return $result;
    }

    /**
     * @return bool|string
     */
    public function getDescription()
    {
        if (is_null($this->eventModel)) {
            throw new Exception('Не указана модель данных');
        }

        $result = [];

        foreach (static::$properties as $key => $property) {
            if (class_exists($property)) {
                /** @var PropertyInterface $property */
                $result[] = (new $property($this->eventModel))->description;
            } else if (isset($this->eventModel->{$key})) {
                /** @var string $property */
                $result[] = Html::tag('b', $property . ': ') . $this->eventModel->{$key};
            }
        }

        return implode('<br />', $result);
    }

    /**
     * @return []
     */
    private function getFullProperties()
    {
        return array_merge(self::$properties, static::$properties);
    }

}