<?php

namespace app\classes\behaviors;

use app\models\ClientAccount;
use app\models\ContractType;
use app\models\Country;
use yii\base\Behavior;
use yii\db\ActiveRecord;


class AccountPriceIncludeVat extends Behavior
{
    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_INSERT => "updatePriceIncludeVat",
            ActiveRecord::EVENT_BEFORE_UPDATE => "updatePriceIncludeVat"
        ];
    }

    public function updatePriceIncludeVat($event)
    {
        /** @var ClientAccount $account */
        $account = $event->sender;
        
        if ($account->country_id != Country::RUSSIA || $account->contract->contract_type_id == ContractType::OPERATOR) {
            $account->price_include_vat = 0;
        } else {
            $account->price_include_vat = 1;
        }
    }

}
