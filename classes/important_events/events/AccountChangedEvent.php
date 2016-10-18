<?php

namespace app\classes\important_events\events;

use app\classes\important_events\events\properties\ClientAccountChangesProperty;
use app\classes\important_events\events\properties\ClientProperty;
use app\classes\important_events\events\properties\DateProperty;
use app\classes\important_events\events\properties\UserProperty;

class AccountChangedEvent extends UnknownEvent
{

    public static
        $properties = [
            'changes' => ClientAccountChangesProperty::class, // Place this at first for correct display
            'date' => DateProperty::class,
            'client' => ClientProperty::class,
            'user_id' => UserProperty::class,
        ];

}