<?php

namespace app\classes\important_events\events;

use app\classes\important_events\events\properties\ClientProperty;
use app\classes\important_events\events\properties\DateProperty;
use app\classes\important_events\events\properties\TroubleProperty;
use app\classes\important_events\events\properties\TroubleStageProperty;
use app\classes\important_events\events\properties\UserProperty;

class SetStateTroubleEvent extends UnknownEvent
{

    public static
        $properties = [
            'date' => DateProperty::class,
            'client' => ClientProperty::class,
            'user' => UserProperty::class,
            'trouble' => TroubleProperty::class,
            'stage' => TroubleStageProperty::class,
        ];

}