<?php

namespace app\classes\important_events\events\sip;

use app\classes\important_events\events\properties\ClientProperty;
use app\classes\important_events\events\properties\DateProperty;
use app\classes\important_events\events\properties\platform\DidIdProperty;
use app\classes\important_events\events\properties\platform\IsSupportProperty;
use app\classes\important_events\events\properties\platform\PhoneIdProperty;
use app\classes\important_events\events\properties\platform\VpbxIdProperty;
use app\classes\important_events\events\UnknownEvent;

class SipCreatedEvent extends UnknownEvent
{

    public static
        $properties = [
            'date' => DateProperty::class,
            'client' => ClientProperty::class,
            'vpbx_id' => VpbxIdProperty::class,
            'did_id' => DidIdProperty::class,
            'phone_id' => PhoneIdProperty::class,
            'is_support' => IsSupportProperty::class,
        ];

}