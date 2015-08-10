<?php

namespace app\classes\behaviors;

use app\models\ClientAccount;
use app\models\ClientContract;
use app\models\ClientContragent;
use yii\base\Behavior;
use yii\db\ActiveRecord;


class ContractContragent extends Behavior
{
    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_UPDATE => "beforeUpdate"
        ];
    }

    public function beforeUpdate($event)
    {
        /** @var ClientContract $contract */
        $contract = $event->sender;
        if ($contract->isAttributeChanged('contragent_id')) {
            /** @var ClientContragent $oldContragent */
            $oldContragent = ClientContragent::findOne($contract->getOldAttribute('contragent_id'));
            /** @var ClientContragent $newContragent */
            $newContragent = ClientContragent::findOne($contract->contragent_id);

            if ($newContragent->country_id != $oldContragent->country_id) {
                $this->updateAccountCountry($contract, $newContragent->country_id);
            }

        }
    }

    private function updateAccountCountry(ClientContract $contract, $countryId)
    {
        $accounts = ClientAccount::findAll(['contract_id' => $contract->id]);
        foreach ($accounts as $account) {
            /** @var ClientAccount $account */
            if ($account->country_id != $countryId) {
                $account->country_id = $countryId;
                $account->save();
            }
        }
    }
}
