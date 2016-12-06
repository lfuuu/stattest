<?php

namespace app\classes\important_events\events;

use app\classes\important_events\events\properties\ClientProperty;
use app\classes\important_events\events\properties\DateProperty;
use app\classes\important_events\events\properties\virtpbx\DidIdProperty;
use app\classes\important_events\events\properties\virtpbx\IsSupportProperty;
use app\classes\important_events\events\properties\virtpbx\VpbxIdProperty;
use app\classes\important_events\events\properties\virtpbx\DidValueProperty;

class VpbxDetachDidEvent extends UnknownEvent
{

    public static
        $properties = [
            'date' => DateProperty::class,
            'client' => ClientProperty::class,
            'vpbx_id' => VpbxIdProperty::class,
            'did_id' => DidIdProperty::class,
            'did' => DidValueProperty::class,
            'is_support' => IsSupportProperty::class,
        ];

}