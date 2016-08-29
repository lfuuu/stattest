<?php

namespace tests\codeception\unit\models;

use app\forms\client\ClientCreateExternalForm;
use app\models\Business;
use app\models\BusinessProcess;
use app\models\BusinessProcessStatus;
use app\models\ClientAccount;
use app\models\ClientContract;
use app\models\ClientContragent;
use app\models\ClientSuper;
use app\models\Country;
use app\models\Organization;
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
        $super = new ClientSuper();
        $super->name = 'test client';
        $super->save();

        $contragent = new ClientContragent();
        $contragent->country_id = Country::RUSSIA;
        $contragent->super_id = $super->id;
        $contragent->ogrn = '';
        $contragent->comment = '';
        $contragent->save();

        $contract = new ClientContract();
        $contract->super_id = $super->id;
        $contract->contragent_id = $contragent->id;
        $contract->business_id = Business::TELEKOM;
        $contract->business_process_id = BusinessProcess::TELECOM_MAINTENANCE;
        $contract->business_process_status_id = BusinessProcessStatus::TELEKOM_MAINTENANCE_WORK;
        $contract->organization_id = Organization::MCN_TELEKOM;
        $contract->number = rand(10000, 99999);
        $contract->save();


        $account = new ClientAccount();
        $account->super_id = $super->id;
        $account->contract_id = $contract->id;
        $account->is_active = 1;
        $account->credit = 1000;
        $account->voip_credit_limit_day = 500;
        $account->client = '';
        $account->sale_channel = 0;
        $account->consignee = '';
        $account->validate();
        $account->save();

        $account->client = 'id' . $account->id;
        $account->save();

        return $account;
    }

}
