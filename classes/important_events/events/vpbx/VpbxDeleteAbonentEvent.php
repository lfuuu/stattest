<?php

namespace app\classes\important_events\events\vpbx;

use app\classes\important_events\events\properties\ClientProperty;
use app\classes\important_events\events\properties\DateProperty;
use app\classes\important_events\events\properties\platform\IsSupportProperty;
use app\classes\important_events\events\properties\platform\VpbxIdProperty;
use app\classes\important_events\events\properties\platform\DidValueProperty;
use app\classes\important_events\events\UnknownEvent;

class VpbxDeleteAbonentEvent extends UnknownEvent
{

    public static
        $properties = [
            'date' => DateProperty::class,
            'client' => ClientProperty::class,
            'vpbx_id' => VpbxIdProperty::class,
            'did' => DidValueProperty::class,
            'is_support' => IsSupportProperty::class,
        ];

}