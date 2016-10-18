<?php

namespace app\classes\important_events\events;

use app\classes\important_events\events\properties\BalanceProperty;
use app\classes\important_events\events\properties\ClientProperty;
use app\classes\important_events\events\properties\CurrentValueProperty;
use app\classes\important_events\events\properties\DateProperty;
use app\classes\important_events\events\properties\LimitProperty;

class DayLimitEvent extends UnknownEvent
{

    public static
        $properties = [
            'date' => DateProperty::class,
            'client' => ClientProperty::class,
            'balance' => BalanceProperty::class,
            'limit' => LimitProperty::class,
            'value' => CurrentValueProperty::class,
        ];

}