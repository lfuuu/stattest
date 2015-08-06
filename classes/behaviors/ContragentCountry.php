<?php

namespace app\classes\behaviors;

use app\dao\ClientGridSettingsDao;
use app\models\ClientAccount;
use app\models\ClientContract;
use app\models\ClientContragent;
use app\models\Country;
use yii\base\Behavior;
use yii\db\ActiveRecord;


class ContragentCountry extends Behavior
{
    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_UPDATE => "beforeUpdate"
        ];
    }

    public function beforeUpdate($event)
    {
        /** @var ClientContragent $contragent */
        $contragent = $event->sender;
        if ($contragent->isAttributeChanged('country_id')) {
            $this->updateAccountCountry($contragent);
        }
    }

    private function updateAccountCountry(ClientContragent $contragent)
    {
        $contracts = ClientContract::findAll(['contragent_id' => $contragent->id]);
        foreach ($contracts as $contract) {
            $accounts = ClientAccount::findAll(['contract_id' => $contract->id]);
            foreach ($accounts as $account) {
                /** @var ClientAccount $account */
                if ($account->country_id != $contragent->country_id) {
                    $account->country_id = $contragent->country_id;
                    $account->save();
                }
            }
        }
    }
}
