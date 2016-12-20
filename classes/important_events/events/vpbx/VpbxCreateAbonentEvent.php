<?php

namespace app\classes\important_events\events\vpbx;

use app\classes\important_events\events\properties\ClientProperty;
use app\classes\important_events\events\properties\DateProperty;
use app\classes\important_events\events\properties\platform\CoreUserIdProperty;
use app\classes\important_events\events\properties\platform\IsSupportProperty;
use app\classes\important_events\events\properties\platform\PhoneNameValueProperty;
use app\classes\important_events\events\properties\platform\PhoneNumberValueProperty;
use app\classes\important_events\events\properties\platform\VpbxIdProperty;
use app\classes\important_events\events\UnknownEvent;

class VpbxCreateAbonentEvent extends UnknownEvent
{

    public static
        $properties = [
            'date' => DateProperty::class,
            'client' => ClientProperty::class,
            'vpbx_id' => VpbxIdProperty::class,
            'phone_number' => PhoneNumberValueProperty::class,
            'phone_name' => PhoneNameValueProperty::class,
            'core_user_id' => CoreUserIdProperty::class,
            'is_support' => IsSupportProperty::class,
        ];

}