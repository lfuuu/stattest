<?php

namespace app\classes\important_events\events;

use app\classes\important_events\events\properties\ClientProperty;
use app\classes\important_events\events\properties\ContragentProperty;
use app\classes\important_events\events\properties\DateProperty;
use app\classes\important_events\events\properties\SuperClientProperty;
use app\classes\important_events\events\properties\UserProperty;

class TransferContragentEvent extends UnknownEvent
{

    public static
        $properties = [
            'date' => DateProperty::class,
            'client' => ClientProperty::class,
            'user_id' => UserProperty::class,
            'contragent' => ContragentProperty::class,
            'super' => SuperClientProperty::class,
        ];

}