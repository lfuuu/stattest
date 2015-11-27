<?php
namespace app\dao;

use Yii;
use app\classes\Singleton;
use app\models\ClientAccount;
use app\models\ClientContragent;

class PartnerDao extends Singleton
{
    public static function getClientsStructure(ClientAccount $account)
    {
        $data = [];
        foreach (ClientContragent::findAll(['partner_contract_id' => $account->id]) as $c) {
            $superId = $c->super_id;
            if (!isset($data[$superId])) {
                $data[$superId] = ['id' => $superId, 'contragents' => []];
            }

            $contracts = [];
            foreach ($c->contracts as $cc) {
                $accounts = [];
                foreach(ClientAccount::findAll(['contract_id' => $cc->id]) as $a) {
                    $accounts[] = $a->id;
                }
                $contracts[] = ['id' => $cc->id, 'accounts' => $accounts];
            }

            $data[$superId]['contragents'][$c->id] = ['id' => $c->id, 'name' => $c->name, 'contracts' => $contracts];

        }
        return $data;
    }
}
