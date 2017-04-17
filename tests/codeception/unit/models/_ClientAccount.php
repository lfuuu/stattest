<?php

namespace tests\codeception\unit\models;

use app\exceptions\ModelValidationException;
use app\forms\client\ClientCreateExternalForm;
use app\models\ClientAccount;
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

    /**
     * оздает одного клиента для тестирования
     * @return \app\models\ClientAccount
     * @throws \Exception
     */
    public static function createOne()
    {
        $clientForm = new ClientCreateExternalForm();
        $clientForm->company = 'test account ' . mt_rand(0, 1000);
        if (!$clientForm->create()) {
            throw new ModelValidationException($clientForm);
        }

        return ClientAccount::findOne(['id' => $clientForm->account_id]);
    }

}
