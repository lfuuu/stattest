<?php

namespace app\classes\important_events\events;

use app\classes\important_events\events\properties\ClientProperty;
use app\classes\important_events\events\properties\DateProperty;
use app\classes\important_events\events\properties\virtpbx\IsSupportProperty;
use app\classes\important_events\events\properties\virtpbx\SipListProperty;
use app\classes\important_events\events\properties\virtpbx\VpbxIdProperty;
use app\classes\important_events\events\properties\virtpbx\DidValueProperty;

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