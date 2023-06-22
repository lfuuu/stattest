<?php

namespace app\classes\event_bus_contragent;

use app\models\EventQueue;

class EventTypeContragentChanged extends EventTypeDefault
{
    const EVENT = 'contragent_changed';

    public static function isThatYourType(ContragentMessage $message): bool
    {
        return $message->getEventType() == self::EVENT;
    }

    public function process(): bool
    {
        EventQueue::go(EventQueue::EVENT_LK_CONTRAGENT_CHANGED, ['contragent_id' => $this->msg->getContragentId()]);

        return true;
    }
}
