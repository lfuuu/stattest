<?php

namespace app\classes\important_events\events;

use app\classes\important_events\events\properties\LoginEmailProperty;
use yii\base\Component;
use yii\base\Exception;
use app\classes\Html;
use app\classes\important_events\events\properties\PropertyInterface;
use app\classes\important_events\events\properties\UnknownProperty;
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
            'login_email' => LoginEmailProperty::class,
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
     * @return array
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
     * @return string
     * @throws Exception
     */
    public function getDescription()
    {
        if (is_null($this->eventModel)) {
            throw new Exception('Не указана модель данных');
        }

        $descriptions = [];

        foreach (static::$properties as $key => $property) {
            $row = null;

            if (class_exists($property)) {
                /** @var PropertyInterface $property */
                $row = (new $property($this->eventModel))->description;
            } elseif (isset($this->eventModel->{$key})) {
                /** @var string $property */
                $row = Html::tag('b', $property . ': ') . $this->eventModel->{$key};
            }

            if (!empty($row)) {
                $descriptions[] = $row;
            }
        }

        $unknownProperties = array_diff(
            array_keys((array)$this->eventModel->properties),
            array_keys(static::$properties)
        );

        if (count($unknownProperties)) {
            $listOfUnknownProperties = '';

            foreach ($unknownProperties as $field) {
                $value = is_array($this->eventModel->properties) ? $this->eventModel->properties[$field] : $this->eventModel->properties->{$field};
                $listOfUnknownProperties .=
                    Html::beginTag('tr') .
                        Html::tag('td', $field) .
                        Html::tag('td', $value) .
                    Html::endTag('tr');
            }

            $descriptions[] =
                Html::beginTag('div', ['class' => 'important-events table-of-changes']) .
                    Html::beginTag('table', ['width' => '100%', 'class' => 'table table-bordered']) .
                        Html::beginTag('tr') .
                            Html::tag('th', 'Поле') .
                            Html::tag('th', 'Значение') .
                        Html::endTag('tr') .
                        $listOfUnknownProperties .
                    Html::endTag('table') .
                Html::endTag('div');
        }

        return implode(Html::tag('br'), $descriptions);
    }

    /**
     * @return array
     */
    private function getFullProperties()
    {
        return array_merge(self::$properties, static::$properties);
    }

}