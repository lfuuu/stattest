<?php

namespace app\classes\event_bus_contragent;


use app\classes\Utils;

class ContragentMessage extends \RdKafka\Message
{
    private ?array $json;

    public function __construct(\RdKafka\Message $message)
    {
        foreach ($message as $field => $value) {
            if (!$value) {
                continue;
            }

            $this->$field = $value;
        }

        $this->json = Utils::fromJson($this->payload);
    }

    public function getEventType(): ?string
    {
        return $this->json['event_type'] ?? false;
    }

    public function getPayload()
    {
        return $this->json['event_data']['payload'] ?? [];
    }

    public function getContragentId(): int
    {
        return $this->getPayload()['contragentId'] ?? 0;
    }

    public function getOperator(): EventTypeDefault
    {
        return EventTypeFactory::getOperator($this);
    }
}