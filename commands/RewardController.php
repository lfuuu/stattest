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
        CalculateReward::calcPartner($partnerContractId, $dateFrom, $dateTo);
    }


    public function actionMoveOldSettings()
    {
        CalculateReward::moveOldSettings();
    }
}
