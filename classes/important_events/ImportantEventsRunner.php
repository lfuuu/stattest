<?php

namespace app\classes\important_events;

use app\classes\actions\message\SendActionFactory;
use app\models\important_events\ImportantEvents;
use app\models\important_events\ImportantEventsProperties;
use app\models\important_events\ImportantEventsRules;

class ImportantEventsRunner
{

    private static $event;

    public static function start(ImportantEvents $event)
    {
        self::$event = $event;

        foreach ($event->rules as $rule) {
            if (self::runConditions($rule)) {
                $action = SendActionFactory::me()->get($rule->action);
                $action->run($rule->template, $event);
            }
        }
    }

    private static function runConditions(ImportantEventsRules $rule)
    {
        $result = 1;

        foreach ($rule->conditions as $condition) {
            if (!$result) {
                break;
            }

            $property = ImportantEventsProperties::findOne(['property' => $condition['property'], 'event_id' => self::$event->id]);
            if (!($property instanceof ImportantEventsProperties)) {
                $property = $value = self::$event->{$condition['property']};
            }
            else {
                $value = $property->value;
            }

            switch ($condition['condition']) {
                case 'isset':
                    $result = $property !== null;
                    break;
                case '<':
                    $result = $value < $condition['value'];
                    break;
                case '>':
                    $result = $value > $condition['value'];
                    break;
                case '<=':
                    $result = $value <= $condition['value'];
                    break;
                case '>=':
                    $result = $value >= $condition['value'];
                    break;
                case '<>':
                    $result = $value != $condition['value'];
                    break;
                case '==':
                    $result = $value == $condition['value'];
                    break;
            }
        }

        return $result;
    }

}