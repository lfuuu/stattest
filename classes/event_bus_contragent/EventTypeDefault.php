<?php

namespace app\classes\event_bus_contragent;

class EventTypeDefault
{
    protected ContragentMessage $msg;
    
    public static function isThatYourType(ContragentMessage $message): bool
    {
        return true;
    }

    public function setMessage(ContragentMessage $msg): self
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
