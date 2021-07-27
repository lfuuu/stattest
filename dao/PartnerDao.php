<?php
namespace app\dao;

use app\models\ClientContract;
use Yii;
use app\classes\Singleton;
use app\models\BusinessProcessStatus;
use app\models\ClientAccount;
use app\models\ClientContragent;
use app\models\ClientSuper;

class PartnerDao extends Singleton
{
    public static function getClientsStructure(ClientAccount $account, $isFullClientInfo = false)
    {
        $data = [];
        $contractTableName = ClientContract::tableName();

        $contragents = ClientContragent::find()
            ->joinWith('contractsActiveQuery')
            ->with('super')
            ->where([$contractTableName . '.partner_contract_id' => $account->contract_id])
            ->indexBy('id')
            ->all();

        /** @var ClientContragent $c */
        foreach ($contragents as $c) {
            $superId = $c->super_id;
            if (!isset($data[$superId])) {
                $data[$superId] = [
                    'id' => $superId,
                    'name' => $c->super->name,
                    'contragents' => []
                ];
            }

            $contracts = [];
            foreach ($c->contracts as $cc) {
                if ($cc->partner_contract_id != null && $cc->partner_contract_id != $account->contract_id) {
                    continue ;
                }

                $accounts = [];
                $accountsExpanded = [];
                foreach (ClientAccount::find()
                             ->where(['contract_id' => $cc->id])
                             ->each() as $a) {
                    $accounts[] = $a->id;
                    if ($isFullClientInfo) {
                        $accountsExpanded[] = ClientSuper::dao()->getAccountInfo($a, $cc);
                    }
                }
            
                $contracts[] = [
                    'id' => $cc->id,
                    'number' => $cc->number,
                    'date' => $cc->document ? $cc->document->contract_date : null,
                    'partner_login_allow' => $cc->is_partner_login_allow,
                    'accounts' => $accounts,
                ] + ($isFullClientInfo ? ['accounts_expanded' => $accountsExpanded] : []);
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
