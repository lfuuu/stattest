<?php

namespace app\classes\event_bus_contragent;

class EventTypeFactory
{
    public static function getOperator($msg): EventTypeDefault
    {
        /** @var EventTypeDefault $operatorClass */
        foreach(self::getTypeOperators() as $operatorClass) {
            if ($operatorClass::isThatYourType($msg)) {
                return (new $operatorClass())->setMessage($msg);
            }
        }

        return (new EventTypeDefault())->setMessage($msg);
    }

    private static function getTypeOperators()
    {
        return [
            EventTypeContragentChanged::class,
//            EventTypeDefault::class,
        ];
    }
}
