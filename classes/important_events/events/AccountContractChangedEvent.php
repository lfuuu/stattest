<?php

namespace app\classes\important_events\events;

use app\classes\important_events\events\properties\AccountContractChangesProperty;
use app\classes\important_events\events\properties\ClientProperty;
use app\classes\important_events\events\properties\DateProperty;
use app\classes\important_events\events\properties\UserProperty;

class AccountContractChangedEvent extends UnknownEvent
{

    public static
        $properties = [
            'changes' => AccountContractChangesProperty::class, // Place this at first for correct display
            'date' => DateProperty::class,
            'client' => ClientProperty::class,
            'user_id' => UserProperty::class,
        ];

}