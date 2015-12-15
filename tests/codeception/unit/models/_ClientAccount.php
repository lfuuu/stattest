<?php

namespace tests\codeception\unit\models;

use app\models\UsageVoip;

class _ClientAccount extends \app\models\ClientAccount
{

    public static $usageId = 0;

    public function getVoipNumbers()
    {
        $numbers = UsageVoip::find()->where(['client' => $this->client, 'type_id' => 'number'])->all();
        $result = [];

        foreach ($numbers as $number) {
            $result[$number->E164] = [
                'type' => 'vpbx',
                'stat_product_id' => self::$usageId,
            ];
        }

        return $result;
    }

}
