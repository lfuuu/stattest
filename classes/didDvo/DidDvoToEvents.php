<?php

namespace app\classes\didDvo;

use app\classes\Utils;

class DidDvoToEvents extends DidDvoTo
{
    protected ?string $readerGroupId = 'stat-diddvo-events-to-events-export';

    protected function proccessMessage($message, $isTest = false): bool
    {
        $data = $message->payload;
        $msg = json_decode($data, true);

        if (!$this->checkMsgAndRegion($msg)) {
            return false;
        }

        $uuid = Utils::genUUID(md5($message->payload));

        $eventToCode = [
            'uncond' => [
                'on' => '10118', // 'On Call Forwarding Unconditional',
                'off' => '10121', // 'Off Call Forwarding Unconditional',
            ],
            'busy' => [
                'on' => '10116', // 'On Call Forwarding on Busy',
                'off' => '10119', // 'Off Call Forwarding on Busy',
            ],
            'noanswer' => [
                'on' => '10117', // 'On Call Forwarding on No Reply',
                'off' => '10120', // 'Off Call Forwarding on No Reply',
            ],
            'unavail' => [
                'on' => '10122', // On Call Forwarding Unavailable
                'off' => '10123', // Off Call Forwarding Unavailable
            ],
            // 'cond' => 10124, // ???
        ];

        if (!isset($eventToCode[$msg['type']][$msg['action']])) {
            if ($msg['type'] != 'cond') {
                throw new \LogicException('Unknown type: ' . $msg['type'] . '-' . $msg['action']);
            }

            echo PHP_EOL . 'no eventToCode found ' . $msg['type'] . '/' . $msg['action'];
            return false;
        }

        $id = "0{$msg['region_id']}-{$uuid}-01";
        $data = <<<TEXT
"{$id}";"{$msg['created_at']}.000000";"0";"{$eventToCode[$msg['type']][$msg['action']]}";"{$msg['did']}";"";"{$msg['number']}";"";"1";"";"{$msg['service_id']}";"";"";"";"";"";"";
TEXT;

        $queryData = [
            'server_id' => $msg['region_id'] + 1000,
            'mcn_callid' => $id,
            'data' => $data,
        ];

        echo PHP_EOL . var_export($queryData);

        return !$isTest
            ? (int)\Yii::$app->dbPg->createCommand()->insert('sorm_itgrad_calls.out_events_package', $queryData)->execute()
            : true;
    }
}