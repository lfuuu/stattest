<?php

namespace app\classes\important_events\events\sip;

use app\classes\important_events\events\properties\ClientProperty;
use app\classes\important_events\events\properties\DateProperty;
use app\classes\important_events\events\properties\platform\IsSupportProperty;
use app\classes\important_events\events\properties\platform\NewIpValueProperty;
use app\classes\important_events\events\properties\platform\SipNumberValueProperty;
use app\classes\important_events\events\UnknownEvent;

class SipChangeAssignIPEvent extends UnknownEvent
{

    public static
        $properties = [
            'date' => DateProperty::class,
            'client' => ClientProperty::class,
            'sip_number' => SipNumberValueProperty::class,
            'new_ip' => NewIpValueProperty::class,
            'is_support' => IsSupportProperty::class,
        ];

}