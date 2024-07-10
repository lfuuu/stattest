<?php

namespace app\modules\sorm\classes\redirects;

use app\classes\Utils;
use app\helpers\DateTimeZoneHelper;
use app\models\EventQueue;

class RedirectListenerVpbxEvents extends RedirectListenerBase
{
    public ?string $eventTopic = 'vpbx_events';

    protected function proccessMessage(\RdKafka\Message $message)
    {
        $payload = Utils::fromJson($message->payload);

        print_r($payload);

        $eventName = $payload['data']['eventName'] ?? null;

        if (!$eventName) {
            echo PHP_EOL . 'no event name';
            return null;
        }

        echo 'event: ' . $eventName;

        if (!in_array($eventName, ['update_forward', 'create_forward_target', 'update_forward_target', 'delete_forward_target'])) {
            echo ' - not in list. Skip';
            return null;
        }

        $did = $payload['data']['did'] ?? null;
        if (!$did) {
            echo ' - DID not found';
            return null;
        }

        echo ' - OK';

        $runTime = DateTimeZoneHelper::getUtcDateTime()
            ->modify('30 second')
            ->format(DateTimeZoneHelper::DATETIME_FORMAT);

        EventQueue::go(EventQueue::SORM_REDIRECT_EXPORT, ['number' => $did], false, $runTime);

        return true;
    }


    public function test()
    {
        $payload = <<<JSON
{
	"id": "d9687e82-914b-4471-a644-dab2ed0ba3c3",
	"data": {
		"did": "74992139115",
		"userId": 157321,
		"enabled": true,
		"eventTs": 1720527542413,
		"regionId": 99,
		"accountId": 61913,
		"eventName": "update_forward",
		"forwardType": "cond",
		"eventVersion": 1,
		"statProductId": 586151,
		"supportUserId": 174797,
		"smartFeatureId": "114992"
	},
	"type": "external_event"
}
JSON;

        $msg = new \RdKafka\Message();
        $msg->payload = $payload;

        $this->proccessMessage($msg);
    }

}
