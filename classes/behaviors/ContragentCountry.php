<?php

namespace app\classes\behaviors;

use app\models\ClientAccount;
use app\models\ClientContract;
use app\models\ClientContragent;
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
        foreach ($contragent->getAccounts() as $account) {
            if ($account->country_id != $contragent->country_id) {
                $account->country_id = $contragent->country_id;
                $account->save();
            }
        }
    }
}
