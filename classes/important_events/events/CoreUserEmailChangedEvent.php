<?php

namespace app\classes\important_events\events;

use app\classes\important_events\events\properties\ClientProperty;
use app\classes\important_events\events\properties\DateProperty;
use app\classes\important_events\events\properties\virtpbx\EmailChangedProperty;
use app\classes\important_events\events\properties\virtpbx\IsSupportProperty;

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