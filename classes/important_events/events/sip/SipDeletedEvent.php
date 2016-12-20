<?php

namespace app\classes\important_events\events\sip;

use app\classes\important_events\events\properties\ClientProperty;
use app\classes\important_events\events\properties\DateProperty;
use app\classes\important_events\events\properties\platform\IsSupportProperty;
use app\classes\important_events\events\properties\platform\SipNumberValueProperty;
use app\classes\important_events\events\UnknownEvent;

class SipDeletedEvent extends UnknownEvent
{

    public static
        $properties = [
            'date' => DateProperty::class,
            'client' => ClientProperty::class,
            'sip_number' => SipNumberValueProperty::class,
            'is_support' => IsSupportProperty::class,
        ];

}