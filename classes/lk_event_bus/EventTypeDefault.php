<?php

namespace app\classes\lk_event_bus;

use app\classes\lk_event_bus\message\LkEventBusMessage;

class EventTypeDefault
{
    protected LkEventBusMessage $msg;
    
    public static function isThatYourType(LkEventBusMessage $message): bool
    {
        return true;
    }

    public function setMessage(LkEventBusMessage $msg): self
    {
        $this->msg = $msg;

        return $this;
    }

    public function process(): bool
    {
        // mosk

        return true;
    }
}
