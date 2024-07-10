<?php

namespace app\modules\sorm\classes\redirects;

use app\classes\adapters\EbcKafka;
use app\classes\Singleton;

abstract class RedirectListenerBase extends Singleton
{
    public ?string $eventTopic = null;
    const readerGroupId = 'stat';

    public function listen()
    {
        EbcKafka::me()->getMessage($this->eventTopic, function ($message) {
            $this->proccessMessage($message);
        }, 60, self::readerGroupId);
    }

    abstract protected function proccessMessage(\RdKafka\Message $message);

    abstract public function test();
}
