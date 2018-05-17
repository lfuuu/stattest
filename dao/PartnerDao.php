<?php
namespace app\dao;

use app\models\ClientContract;
use Yii;
use app\classes\Singleton;
use app\models\ClientAccount;
use app\models\ClientContragent;

class PartnerDao extends Singleton
{
    public static function getClientsStructure(ClientAccount $account)
    {
        $data = [];
        $contractTableName = ClientContract::tableName();
        $contragentTableName = ClientContragent::tableName();

        $contragents = ClientContragent::find()
            ->joinWith('contractsActiveQuery')
            ->where([
                'OR',
                [
                    // Из contragent, если партнера нет в contract
                    'AND',
                    [$contragentTableName . '.partner_contract_id' => $account->contract_id],
                    [$contractTableName . '.partner_contract_id' => null],
                ],
                // Из contract
                [$contractTableName . '.partner_contract_id' => $account->contract_id],
            ])
            ->indexBy('id')
            ->all();

        /** @var ClientContragent $c */
        foreach ($contragents as $c) {
            $superId = $c->super_id;
            if (!isset($data[$superId])) {
                $data[$superId] = [
                    'id' => $superId,
                    'contragents' => []
                ];
            }

            $contracts = [];
            foreach ($c->contracts as $cc) {
                if ($cc->partner_contract_id != null && $cc->partner_contract_id != $account->contract_id) {
                    continue ;
                }

                $accounts = [];
                foreach (ClientAccount::find()
                             ->where(['contract_id' => $cc->id])
                             ->each() as $a) {
                    $accounts[] = $a->id;
                }

                $contracts[] = [
                    'id' => $cc->id,
                    'number' => $cc->number,
                    'date' => $cc->document ? $cc->document->contract_date : null,
                    'accounts' => $accounts,
                ];
            }

            $data[$superId]['contragents'][$c->id] = [
                'id' => $c->id,
                'name' => $c->name,
                'contracts' => $contracts
            ];

        }
        return $data;
    }

    /**
     * @return array
     */
    public static function getHelpConfluence()
    {
        return ['confluenceId' => 25887631, 'message' => 'Партнер'];
    }
}
