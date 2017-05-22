<?php

namespace app\classes\important_events\events;

use app\classes\important_events\events\properties\ClientProperty;
use app\classes\important_events\events\properties\DateProperty;
use app\classes\important_events\events\properties\platform\ConfirmUrlProperty;
use app\classes\important_events\events\properties\platform\IsSupportProperty;
use app\classes\important_events\events\properties\platform\LoginValueProperty;
use app\classes\important_events\events\UnknownEvent;

class PasswordRestoreEvent extends UnknownEvent
{

    public static
        $properties = [
            'date' => DateProperty::class,
            'client' => ClientProperty::class,
            'login' => LoginValueProperty::class,
            'is_support' => IsSupportProperty::class,
            'confirm_url' => ConfirmUrlProperty::class,
        ];

}