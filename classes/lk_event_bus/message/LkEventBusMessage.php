<?php

namespace app\classes\lk_event_bus\message;


use app\classes\lk_event_bus\EventTypeDefault;
use app\classes\lk_event_bus\EventTypeFactory;
use app\classes\Utils;

class LkEventBusMessage extends \RdKafka\Message
{
    private ?array $json;
    public ?\RdKafka\Message $_message;

    public function __construct(\RdKafka\Message $message)
    {
        $this->_message = $message;

        foreach ($message as $field => $value) {
            if (!$value) {
                continue;
            }

            $this->$field = $value;
        }

        $this->json = Utils::fromJson($message->payload);
    }

    public function getEventType(): ?string
    {
        return $this->json['event_type'] ?? false;
    }

    public function getId(): ?string
    {
        return $this->json['id'] ?? null;
    }

    public function getPayload()
    {
        return $this->json['event_data']['payload'] ?? [];
    }

    public function getOperator(): EventTypeDefault
    {
        return EventTypeFactory::getOperator($this);
    }
}