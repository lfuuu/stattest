<?php

namespace app\classes\important_events;

use yii\helpers\Inflector;
use app\classes\important_events\events\UnknownEvent;
use app\models\important_events\ImportantEvents;

abstract class ImportantEventsDetailsFactory
{

    const EVENTS_NAMESPACE = 'app\\classes\\important_events\\events\\';
    const DEFAULT_EVENT = 'unknown_event';

    /**
     * @param $eventName
     * @param ImportantEvents|null $eventModel
     * @return UnknownEvent
     */
    public static function get($eventName, ImportantEvents $eventModel = null)
    {
        $eventNamespace = substr($eventName, 0, strpos($eventName, '_'));

        $classNames = [
           self::EVENTS_NAMESPACE . $eventNamespace . '\\' . Inflector::camelize($eventName . '_event'),
           self::EVENTS_NAMESPACE . Inflector::camelize($eventName . '_event'),
           self::EVENTS_NAMESPACE . Inflector::camelize(self::DEFAULT_EVENT),
        ];

        foreach ($classNames as $className) {
            if (class_exists($className)) {
                return new $className($eventModel);
            }
        }

        throw new \LogicException('Cant find event class by name "' . $eventName . '"');
    }

}