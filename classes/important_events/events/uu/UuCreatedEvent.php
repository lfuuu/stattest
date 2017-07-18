<?php

namespace app\classes\important_events\events\uu;

use app\classes\important_events\events\properties\ClientProperty;
use app\classes\important_events\events\properties\DateProperty;
use app\classes\important_events\events\properties\uu\AccountTariffProperty;
use app\classes\important_events\events\properties\uu\ServiceTypeProperty;
use app\classes\important_events\events\UnknownEvent;

class UuCreatedEvent extends UnknownEvent
{

    public static $properties = [
        'date' => DateProperty::class,
        'client' => ClientProperty::class,
        'account_tariff_id' => AccountTariffProperty::class,
        'service_type_id' => ServiceTypeProperty::class,
    ];

}