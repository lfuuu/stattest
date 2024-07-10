<?php

namespace app\modules\sorm\classes\redirects;

use app\classes\Utils;
use app\helpers\DateTimeZoneHelper;
use app\models\EventQueue;

class RedirectListenerDidFwd extends RedirectListenerBase
{
    public ?string $eventTopic = 'vpbx-old.vpbx.did_forward';

    protected function proccessMessage(\RdKafka\Message $message)
    {
        $payload = Utils::fromJson($message->payload);

        $didId = $payload['after']['did_id'] ?? $payload['before']['did_id'] ?? null;

        if (!$didId) {
            echo PHP_EOL . '(-) didId not found';
            return null;
        }

        echo PHP_EOL . '(+) DidId: ' . $didId;

        $runTime = DateTimeZoneHelper::getUtcDateTime()
            ->modify('20 second')
            ->format(DateTimeZoneHelper::DATETIME_FORMAT);

//        \app\modules\sorm\classes\RedirectCollectorDao::me()->makeExportEventByDidId($didId);
        EventQueue::go(EventQueue::SORM_EXPORT_BY_DID_ID, ['did_id' => $didId], false, $runTime);

        return true;
    }


    public function test()
    {
        $payload = <<<JSON
{
	"before": null,
	"after": {
		"did_forward_id": 1535405,
		"did_id": 556514,
		"type": "noanswer",
		"enabled": false,
		"did_timecondition_id": null,
		"did_announce_id": null,
		"strategy": "rr",
		"timeout": 20,
		"callerid_diversion": false,
		"update_at": "2024-07-10T10:33:49.179818Z"
	},
	"source": {
		"version": "1.9.0.Final",
		"connector": "postgresql",
		"name": "vpbx-old",
		"ts_ms": 1720607629626,
		"snapshot": "false",
		"db": "vpbx",
		"sequence": "[\"21142887401216\",\"21142887137160\"]",
		"schema": "vpbx",
		"table": "did_forward",
		"txId": 3828337954,
		"lsn": 21142887137160,
		"xmin": null
	},
	"op": "c",
	"ts_ms": 1720607629939,
	"transaction": null
}
JSON;

        $msg = new \RdKafka\Message();
        $msg->payload = $payload;

        $this->proccessMessage($msg);
    }

}
