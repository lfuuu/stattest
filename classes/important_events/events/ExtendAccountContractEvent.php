<?php

namespace app\classes\important_events\events;

use app\classes\important_events\events\properties\ClientProperty;
use app\classes\important_events\events\properties\ContractProperty;
use app\classes\important_events\events\properties\DateProperty;
use app\classes\important_events\events\properties\UserProperty;

class ExtendAccountContractEvent extends UnknownEvent
{

    public static
        $properties = [
            'date' => DateProperty::class,
            'client' => ClientProperty::class,
            'user_id' => UserProperty::class,
            'contract' => ContractProperty::class,
        ];

}