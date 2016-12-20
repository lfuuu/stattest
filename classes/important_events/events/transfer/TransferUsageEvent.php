<?php

namespace app\classes\important_events\events\transfer;

use app\classes\important_events\events\properties\ClientProperty;
use app\classes\important_events\events\properties\DateProperty;
use app\classes\important_events\events\properties\UsageTransferProperty;
use app\classes\important_events\events\properties\UserProperty;
use app\classes\important_events\events\UnknownEvent;

class TransferUsageEvent extends UnknownEvent
{

    public static
        $properties = [
            'date' => DateProperty::class,
            'client' => ClientProperty::class,
            'user' => UserProperty::class,
            'usage_transfer' => UsageTransferProperty::class,
        ];

}