<?php

namespace app\modules\sorm\classes\didDvo;

use app\classes\adapters\EbcKafka;

class DidDvoToCdr extends DidDvoTo
{
    protected ?string $readerGroupId = 'stat-diddvo-events-to';

    protected function proccessMessage($message, $isTest = false): bool
    {
        $data = $message->payload;
        $msg = json_decode($data, true);

        if (!$this->checkMsgAndRegion($msg)) {
            return false;
        }

        $queryData = [
            'did' => $msg['did'],
            'type' => $msg['type'],
            'number' => $msg['number'],
            'is_on' => $msg['action'] == 'on',
            'created_at' => $msg['created_at'] . '+00',
            'region_id' => $msg['region_id'],
        ];

        print_r($queryData);
        return !$isTest
            ? \Yii::$app->dbPg->createCommand()->insert('sorm_itgrad.did_forwarding', $queryData)->execute()
            : true;
    }

}