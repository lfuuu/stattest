<?php

namespace app\classes\important_events\events\sip;

use app\classes\important_events\events\properties\ClientProperty;
use app\classes\important_events\events\properties\DateProperty;
use app\classes\important_events\events\properties\platform\AttachedValueProperty;
use app\classes\important_events\events\properties\platform\IsSupportProperty;
use app\classes\important_events\events\properties\platform\PasswordValueProperty;
use app\classes\important_events\events\properties\platform\SipNumberValueProperty;
use app\classes\important_events\events\UnknownEvent;

class SipChangePasswordEvent extends UnknownEvent
{

    public static
        $properties = [
            'date' => DateProperty::class,
            'client' => ClientProperty::class,
            'sip_number' => SipNumberValueProperty::class,
            'password' => PasswordValueProperty::class,
            'attached' => AttachedValueProperty::class,
            'is_support' => IsSupportProperty::class,
        ];

}