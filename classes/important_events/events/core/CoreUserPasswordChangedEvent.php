<?php

namespace app\classes\important_events\events\core;

use app\classes\important_events\events\properties\ClientProperty;
use app\classes\important_events\events\properties\DateProperty;
use app\classes\important_events\events\properties\platform\IsSupportProperty;
use app\classes\important_events\events\properties\platform\LoginValueProperty;
use app\classes\important_events\events\properties\platform\PasswordValueProperty;
use app\classes\important_events\events\UnknownEvent;

class CoreUserPasswordChangedEvent extends UnknownEvent
{

    public static
        $properties = [
            'date' => DateProperty::class,
            'client' => ClientProperty::class,
            'login' => LoginValueProperty::class,
            'password' => PasswordValueProperty::class,
            'is_support' => IsSupportProperty::class,
        ];

}