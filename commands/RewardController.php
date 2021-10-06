<?php

namespace app\commands;

use ActiveRecord\ModelException;
use app\classes\rewards\CalculateReward;
use app\models\Bill;
use app\models\ClientContract;
use app\models\rewards\RewardClientContractService;
use DateTime;
use yii\base\InvalidParamException;
use yii\console\Controller;

class RewardController extends Controller
{
    public function actionCalculateBill($bill_no)
    {
        $bill = Bill::find()->where(['bill_no' => $bill_no])->one();

        if (!$bill) {
            throw new ModelException($bill);
        }

        CalculateReward::processBill($bill);
    }

    public function actionCalculateAllPartners($dateFrom = 'yesterday', $dateTo = null)
    {
        $partnerContractIds = RewardClientContractService::find()
            ->select('client_contract_id')
            ->distinct()
            ->column();

        if (!$partnerContractIds) {
            throw new InvalidParamException('Партнеры с выставленными настройками не найдены');
        }

        $this->actionCalculatePartner($partnerContractIds, $dateFrom, $dateTo);
    }

    public function actionCalculatePartner($partnerContractId, $dateFrom = null, $dateTo = null)
    {
        $this->_calcPartner($partnerContractId, $dateFrom, $dateTo);
    }

    public function _calcPartner($partnerContractIds, $dateFrom, $dateTo)
    {
        $referredClients = ClientContract::find()
            ->select('c.id')
            ->joinWith('clientAccountModels as c')
            ->where(['partner_contract_id' => $partnerContractIds])
            ->column();

        if (!$referredClients) {
            throw new InvalidParamException('Клиенты не найдены');
        }

        $this->_findBills($referredClients, $dateFrom, $dateTo, $partnerContractIds);
    }

    private function _findBills($referredClients, $dateFrom, $dateTo, $partnerContractIds)
    {
        $billQuery = Bill::find()
            ->where(['client_id' => $referredClients])
            ->andWhere(['is_payed' => 1])
            ->andWhere(['>=', 'payment_date', (new DateTime($dateFrom))->format('Y-m-d')]);
        
        $dateTo && $billQuery->andWhere(['<', 'payment_date', $dateTo]);
        
        foreach ($billQuery->each() as $bill) {
            try {
                CalculateReward::processBill($bill, $partnerContractIds);
            } catch (\Exception $e) {
                echo '[ERROR] CЧЕТ ' . $bill->bill_no . ': ' . $e->getMessage() . PHP_EOL;
            }
        }
    }

    public function actionMoveOldSettings()
    {
        CalculateReward::moveOldSettings();
    }
}
