<?php

namespace app\classes\lk_event_bus;

use app\classes\lk_event_bus\message\LkContractChangeMessage;
use app\classes\lk_event_bus\message\LkEventBusMessage;
use app\models\EventQueue;

class EventTypeContractChanged extends EventTypeDefault
{
    const EVENT = 'contract_changed';

    protected LkContractChangeMessage $cmsg;

    public static function isThatYourType(LkEventBusMessage $message): bool
    {
        return $message->getEventType() == self::EVENT;
    }

    public function setMessage(LkEventBusMessage $msg): self
    {
        parent::setMessage($msg);

        $this->cmsg = (new LkContractChangeMessage($msg->_message));

        return $this;
    }

    public function process(): bool
    {
        EventQueue::go(EventQueue::EVENT_LK_CONTRACT_CHANGED, [
            'contract_id' => $this->cmsg->getContractId(),
            'organization_id' => $this->cmsg->getOrganizationId(),
            'event_id' => $this->cmsg->getId(),
        ]);

        return true;
    }
}
