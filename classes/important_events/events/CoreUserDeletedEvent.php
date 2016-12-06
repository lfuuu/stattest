<?php

namespace app\classes\important_events\events;

use app\classes\important_events\events\properties\ClientProperty;
use app\classes\important_events\events\properties\DateProperty;
use app\classes\important_events\events\properties\virtpbx\IsSupportProperty;
use app\classes\important_events\events\properties\virtpbx\LoginProperty;

class CoreUserDeletedEvent extends UnknownEvent
{

    public static
        $properties = [
            'date' => DateProperty::class,
            'client' => ClientProperty::class,
            'login' => LoginProperty::class,
            'is_support' => IsSupportProperty::class,
        ];

}