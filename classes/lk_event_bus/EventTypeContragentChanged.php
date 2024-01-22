<?php

namespace app\classes\lk_event_bus;

use app\classes\lk_event_bus\message\LkContragentChangeMessage;
use app\classes\lk_event_bus\message\LkEventBusMessage;
use app\models\EventQueue;

class EventTypeContragentChanged extends EventTypeDefault
{
    const EVENT = 'contragent_changed';

    public static function isThatYourType(LkEventBusMessage $message): bool
    {
        return $message->getEventType() == self::EVENT;
    }

    public function setMessage(LkEventBusMessage $msg): self
    {
        $this->msg = (new LkContragentChangeMessage($msg->_message));

        return $this;
    }

    public function process(): bool
    {
        EventQueue::go(EventQueue::EVENT_LK_CONTRAGENT_CHANGED, [
            'contragent_id' => $this->msg->getContragentId(),
            'event_id' => $this->msg->getId(),
        ]);

        return true;
    }
}
