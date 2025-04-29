<?php

namespace app\commands;

use app\classes\Assert;
use app\exceptions\ModelValidationException;
use app\models\ClientContract;
use app\models\ClientContractAdditionalAgreement;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * Контакты
 */
class ContractController extends Controller
{
    /**
     * Функция переключает организацию по созданным client_contract_additional_agreement
     *
     * insert into client_contract_additional_agreement
     * select null, contract_id, id, 14, 1, '2025-05-01'
     * from clients c
     * where c.id in (60027 ... )
     *
     * @param $contragentId
     * @param $fromId
     * @param $toId
     * @param $date
     * @return void
     * @throws \yii\base\Exception
     */
    public function actionSetCompany($date): int
    {
        $query = ClientContractAdditionalAgreement::find()->where(['transfer_date' => $date])
            ->limit(1);  //
        /** @var ClientContractAdditionalAgreement $agreenent */
        foreach ($query->each() as $agreenent) {
            echo PHP_EOL . 'contract/account: ' . $agreenent->contract_id . '/' . $agreenent->account_id;
            $contract = ClientContract::find()->where(['id' => $agreenent->contract_id])->one();
            Assert::isObject($contract);
            /** @var ClientContract $contract */
            $contract = $contract->loadVersionOnDate($agreenent->transfer_date);
            if ($contract->organization_id != $agreenent->to_organization_id) {
                echo ' (+)';
                $contract->setHistoryVersionStoredDate($agreenent->transfer_date);
                $contract->organization_id = $agreenent->to_organization_id;
                if (!$contract->save()) {
                    throw new ModelValidationException($contract);
                }
            } else {
                echo ' (-)';
            }
        }

        return ExitCode::OK;
    }
}
