<?php

namespace app\classes\important_events\events\core;

use app\classes\important_events\events\properties\ClientProperty;
use app\classes\important_events\events\properties\DateProperty;
use app\classes\important_events\events\properties\platform\EmailChangedProperty;
use app\classes\important_events\events\properties\platform\IsSupportProperty;
use app\classes\important_events\events\UnknownEvent;

class CoreUserEmailChangedEvent extends UnknownEvent
{

    public static
        $properties = [
            'date' => DateProperty::class,
            'client' => ClientProperty::class,
            'email' => EmailChangedProperty::class,
            'is_support' => IsSupportProperty::class,
        ];

}