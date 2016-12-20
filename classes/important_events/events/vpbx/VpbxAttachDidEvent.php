<?php

namespace app\classes\important_events\events\vpbx;

use app\classes\important_events\events\properties\ClientProperty;
use app\classes\important_events\events\properties\DateProperty;
use app\classes\important_events\events\properties\platform\IsSupportProperty;
use app\classes\important_events\events\properties\platform\SipListProperty;
use app\classes\important_events\events\properties\platform\VpbxIdProperty;
use app\classes\important_events\events\properties\platform\DidValueProperty;
use app\classes\important_events\events\UnknownEvent;

class VpbxAttachDidEvent extends UnknownEvent
{

    public static
        $properties = [
            'date' => DateProperty::class,
            'client' => ClientProperty::class,
            'vpbx_id' => VpbxIdProperty::class,
            'did' => DidValueProperty::class,
            'sip_list' => SipListProperty::class,
            'is_support' => IsSupportProperty::class,
        ];

}