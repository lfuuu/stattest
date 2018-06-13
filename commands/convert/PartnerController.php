<?php

namespace app\commands\convert;

use app\exceptions\ModelValidationException;
use app\models\Business;
use app\models\BusinessProcessStatus;
use app\models\ClientContract;
use app\models\ClientContragent;
use app\models\User;
use yii\console\Controller;

class PartnerController extends Controller
{
    /**
     * Заполнение партнеров
     */
    public function actionFill()
    {

        $userToContractId = $this->_getUserToContractId();

        $query = ClientContract::find()
            ->alias('cc')
            ->innerJoin(['cg' => ClientContragent::tableName()], 'cc.contragent_id = cg.id')
            ->where([
                'cc.partner_contract_id' => null,
                'cg.partner_contract_id' => null,
                'cc.account_manager' => array_keys($userToContractId),
                'cc.business_process_status_id' => [
                    BusinessProcessStatus::TELEKOM_MAINTENANCE_CONNECTED,
                    BusinessProcessStatus::TELEKOM_MAINTENANCE_WORK,
                ]
            ]);

        $transaction = ClientContract::getDb()->beginTransaction();
        try {
            $count = 0;
            /** @var ClientContract $contract */
            foreach ($query->each() as $contract) {

                if ($count % 100 == 0) {
                    $transaction->commit();
                    $transaction = ClientContract::getDb()->beginTransaction();
                    echo PHP_EOL . '...saving';
                }

                $account = reset($contract->getAccounts(false));
                echo PHP_EOL . ($count++) . ': ' . $contract->id . ' a: ' . $account->id . ' -> ' . $userToContractId[$contract->account_manager] . ' (' . $contract->account_manager . ')';

                if ($contract->partner_contract_id == $userToContractId[$contract->account_manager]) {
                    continue;
                }

                $contract->partner_contract_id = $userToContractId[$contract->account_manager];

                if (!$contract->save()) {
                    throw new ModelValidationException($contract);
                }
            }
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();

            throw $e;
        }
    }

    private function _getUserToContractId()
    {
        $data = ClientContragent::find()
            ->alias('cg')
            ->innerJoinWith(['contractsActiveQuery cc'])
            ->innerJoin(['u' => User::tableName()], 'u.name like concat(cg.name, \'%\')')
            ->where([
                'cc.business_id' => Business::PARTNER
            ])
            ->andWhere(['NOT', [
                'u.name' => ['Test', 'Коробков Борис']
            ]
            ])
            ->select(['cc.id'])
            ->indexBy('user')
            ->asArray()
            ->column();

        return $data;

    }


}