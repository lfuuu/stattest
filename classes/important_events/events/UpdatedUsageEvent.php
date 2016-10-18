<?php

namespace app\classes\important_events\events;

use app\classes\important_events\events\properties\ClientProperty;
use app\classes\important_events\events\properties\DateProperty;
use app\classes\important_events\events\properties\UsageChangesProperty;
use app\classes\important_events\events\properties\UsageProperty;
use app\classes\important_events\events\properties\UserProperty;

class UpdatedUsageEvent extends UnknownEvent
{

    public static
        $properties = [
            'changes' => UsageChangesProperty::class, // Place this at first for correct display
            'date' => DateProperty::class,
            'client' => ClientProperty::class,
            'user' => UserProperty::class,
            'usage' => UsageProperty::class,
        ];

}