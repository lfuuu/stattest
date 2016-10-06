<?php

namespace app\classes\important_events\events;

use app\classes\important_events\events\properties\BalanceProperty;
use app\classes\important_events\events\properties\BeforeValueProperty;
use app\classes\important_events\events\properties\ClientProperty;
use app\classes\important_events\events\properties\CurrentValueProperty;
use app\classes\important_events\events\properties\DateProperty;

class ChangeCreditLimitEvent extends UnknownEvent
{

    public static
        $properties = [
            'date' => DateProperty::class,
            'client' => ClientProperty::class,
            'balance' => BalanceProperty::class,
            'before' => BeforeValueProperty::class,
            'value' => CurrentValueProperty::class,
        ];

}