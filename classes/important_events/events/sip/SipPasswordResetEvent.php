<?php

namespace app\classes\important_events\events\sip;

use app\classes\important_events\events\properties\ClientProperty;
use app\classes\important_events\events\properties\DateProperty;
use app\classes\important_events\events\properties\platform\BodyValueProperty;
use app\classes\important_events\events\properties\platform\EmailValueProperty;
use app\classes\important_events\events\properties\platform\IsSupportProperty;
use app\classes\important_events\events\UnknownEvent;

class SipPasswordResetEvent extends UnknownEvent
{

    public static
        $properties = [
            'date' => DateProperty::class,
            'client' => ClientProperty::class,
            'email' => EmailValueProperty::class,
            'is_support' => IsSupportProperty::class,
            'body' => BodyValueProperty::class,
        ];

}