<?php

namespace app\classes\rewards;

use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\Bill;
use app\models\BillLine;
use app\models\ClientAccount;
use app\models\ClientContract;
use app\models\ClientContractReward;
use app\models\OperationType;
use app\models\rewards\RewardBill;
use app\models\rewards\RewardBillLine;
use app\models\rewards\RewardClientContractResource;
use app\models\rewards\RewardClientContractService;
use app\models\rewards\RewardsServiceTypeResource;
use app\modules\uu\models\AccountEntry;
use DateTimeImmutable;
use Exception;
use LogicException;
use Yii;
use yii\base\InvalidParamException;
use yii\helpers\ArrayHelper;

class CalculateReward
{
    public static $servicesSettings = [];

    public static function processBill($bill, $partnerContractId = null)
    {
        if (!$bill) {
            throw new LogicException('Счет не найден ' . $bill->bill_no);
        }
        
        if (!$bill->payment_date) {
            throw new InvalidParamException('Счет не оплачен  ' . $bill->bill_no);
        }
        
        if (!$partnerContractId || is_array($partnerContractId)) {
            if (!($partnerContractId = $bill->clientAccount->contract->partner_contract_id)) {
                throw new InvalidParamException('У клиента ' . $bill->client_id . ' не выставлен партнер');
            }
        }

        echo 'СЧЕТ: ' . $bill->bill_no . ' ' . $partnerContractId .  PHP_EOL;
        if ($rewardBill = RewardBill::findOne(['bill_id' => $bill->id])) {
            $rewardBill->delete();
        }

        $settings = self::_getPartnerRewardSettings($partnerContractId);
        if (!$settings) {
            throw new Exception('Настройки не выставлены у партнера ' . $partnerContractId);
        }

        $currentSettings = self::_getCurrentSettings($settings, $bill->payment_date);
        if (!$currentSettings) {
            throw new Exception('У партнера ' . $partnerContractId . ' подходящие настройки отсутствуют');
        }

        $rewards = self::_calculateRewards($bill, $currentSettings);
        if ($rewards) {
            self::_saveRewards($rewards, $bill, $partnerContractId);
        }
    }

    private static function _getPartnerRewardSettings($partnerContractId)
    {
        if (!isset(self::$servicesSettings[$partnerContractId])) {
            $rewardSettings = RewardClientContractService::find()
                ->joinWith('resources')
                ->where(['client_contract_id' => $partnerContractId])
                ->orderBy(['actual_from' => SORT_DESC])
                ->asArray()
                ->all();
            
            $rewardSettings = ArrayHelper::index($rewardSettings, 'actual_from', ['service_type_id']);
            self::$servicesSettings[$partnerContractId] = $rewardSettings;
        }

        return self::$servicesSettings[$partnerContractId];
    }

    private static function _getCurrentSettings($settings, $paymentDate)
    {
        $currentSettings = [];
        foreach ($settings as $serviceTypeId => $settingsByDate) {
            foreach ($settingsByDate as $date => $setting) {
                if ($date <= $paymentDate) {
                    $currentSettings[$serviceTypeId] = $setting;
                    break;
                }   
            }
        }

        return $currentSettings;
    }

    private static function _calculateRewards($bill, $currentSetting)
    {
        $totalSum = 0;
        $rewardBillLines = [];
        $paymentDate = new DateTimeImmutable($bill->payment_date);
        foreach ($bill->lines as $line) {
            $rewardSum = 0;
            if (!($line->uu_account_entry_id && $line->id_service)){
                continue;
            }

            if (!(isset($line->accountEntry) && isset($line->accountTariff))) {
                continue;
            }
            $accountTariff = $line->accountTariff;
            $accountEntry = $line->accountEntry;

            if (!isset($currentSetting[$accountTariff->service_type_id])) {
                continue;
            }

            $reward = $currentSetting[$accountTariff->service_type_id];
            $rewardActualFrom = new DateTimeImmutable($reward['actual_from']);
            if ($reward['period_type'] == RewardClientContractService::PERIOD_MONTH) {
                $payableDate = $rewardActualFrom->modify('+' . $reward['period_month'] . ' months');
                if ($paymentDate > $payableDate) {
                    continue;
                }
            } elseif ($reward['period_type'] != RewardClientContractService::PERIOD_ALWAYS) {
                continue;
            }

            $lineSum = $line->sum_without_tax;
            $log = 'Сумма (' . $lineSum . ') * ' . $accountTariff->serviceType['name'];
            if ($accountEntry->type_id < 0) {
                $isOnceOnly = false;
                switch ($accountEntry->type_id) {
                    case AccountEntry::TYPE_ID_MIN:
                        $rewardType = $reward['percentage_of_minimal'];
                        $log .= ' ' . RewardClientContractService::$rewardTypes[RewardClientContractService::PERCENTAGE_OF_MINIMAL];
                        break;
                    case AccountEntry::TYPE_ID_SETUP:
                        $diff = $paymentDate->diff($rewardActualFrom);
                        if (isset($reward['once_only']) && $diff->m < 1) {
                            $isOnceOnly = true;
                            $rewardType = $reward['once_only'];
                            $log .= ' ' . RewardClientContractService::$rewardTypes[RewardClientContractService::ONCE_ONLY];
                            break;
                        }
                        $rewardType = $reward['percentage_once_only'];
                        $log .= ' ' . RewardClientContractService::$rewardTypes[RewardClientContractService::PERCENTAGE_ONCE_ONLY];
                        break;
                    default:
                        $rewardType = $reward['percentage_of_fee'];
                        $log .= ' ' . RewardClientContractService::$rewardTypes[RewardClientContractService::PERCENTAGE_OF_FEE];
                        break;
                }
                if ($isOnceOnly) {
                    $rewardSum = $rewardType;
                    $log .= ' ' . '(' . $rewardType . ')';
                } else {
                    $rewardSum = $lineSum * $rewardType / 100;
                    $log .= ' ' . '(' . $rewardType . '%)';
                }
            } else {
                if (isset($reward['resources'][$accountEntry->tariffResource->resource_id])) {
                    $resource = $reward['resources'][$accountEntry->tariffResource->resource_id];
                    $rewardSum = $lineSum * $resource['price_percent'] / 100;
                    $log .= ': ' . $accountEntry->tariffResource->resource['name'] . ' ' . '(' . $resource['price_percent'] . '%)';
                }
            }

            if (!$rewardSum) {
                continue;
            }

            $totalSum += $rewardSum;
            $rewardBillLines[] = [
                'bill_id' => $bill->id,
                'bill_line_pk' => $line->pk,
                'sum' => $rewardSum,
                'log' => $log,
            ];
        }
        return [
            'rewardBillLines' => $rewardBillLines,
            'totalSum' => $totalSum,
        ]; 
    }

    private static function _saveRewards($rewards, $bill, $partnerContractId)
    {
        $totalSum = $rewards['totalSum'];
        $rewardBillLines = $rewards['rewardBillLines'];

        $transaction = Yii::$app->db->beginTransaction();
        try {
            \Yii::$app->db->createCommand()->insert(
                RewardBill::tableName(),
                [
                    'bill_id' => $bill->id,
                    'partner_id' => $partnerContractId,
                    'client_id' => $bill->client_id,
                    'payment_date' => $bill->payment_date,
                    'sum' => $totalSum
                ]
            )->execute();

            \Yii::$app->db->createCommand()->batchInsert(
                RewardBillLine::tableName(),
                [
                    'bill_id',
                    'bill_line_pk',
                    'sum',
                    'log'
                ],
                $rewardBillLines
            )->execute();
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    public static function moveOldSettings()
    {
        RewardClientContractService::deleteAll();
        RewardClientContractResource::deleteAll();
        $oldContractSettings = ClientContractReward::find()->asArray()->all();
        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($oldContractSettings as $setting) {
                $newServiceSetting = new RewardClientContractService();
                $newServiceSetting->actual_from = $setting['actual_from'];
                $newServiceSetting->client_contract_id = $setting['contract_id'];
                $newServiceSetting->insert_time = $setting['insert_time'];
                $newServiceSetting->user_id = $setting['user_id'];
                if ($setting['usage_type'] == 'usage_voip') {
                    $newServiceSetting->service_type_id = RewardClientContractService::SERVICE_VOIP;
    
                } elseif ($setting['usage_type'] == 'usage_virtpbx') {
                    $newServiceSetting->service_type_id = RewardClientContractService::SERVICE_VIRTPBX;
    
                } elseif ($setting['usage_type'] == 'usage_call_chat') {
                    $newServiceSetting->service_type_id = RewardClientContractService::SERVICE_CALL_CHAT;
    
                } else {
                    $newServiceSetting->service_type_id = RewardClientContractService::SERVICE_TRUNK;
                }
                
                $newServiceSetting->period_type = $setting['period_type'];
    
                if ($setting['percentage_of_fee']) {
                    $newServiceSetting->percentage_of_fee = $setting['percentage_of_fee'];
                    
                }
                if ($setting['percentage_once_only']) {
                    $newServiceSetting->percentage_once_only = $setting['percentage_once_only'];
                }
    
                if ($setting['percentage_of_fee']) {
                    $newServiceSetting->percentage_of_fee = $setting['percentage_of_fee'];
                }
    
                if ($setting['period_type'] == 'month') {
                    $newServiceSetting->period_type = 'month';
                    $newServiceSetting->period_month = $setting['period_month'];
                } else {
                    $newServiceSetting->period_type = 'always';
                }
    
                if (!$newServiceSetting->save()) {
                    throw new ModelValidationException($newServiceSetting);
                }
    
                if ($setting['percentage_of_over'] > 0) {
                    $serviceResources = RewardsServiceTypeResource::find()
                        ->where(['service_type_id' => $newServiceSetting->service_type_id])
                        ->asArray()
                        ->all();
                        
                    foreach ($serviceResources as $serviceResource) {
                        $resource = new RewardClientContractResource();
                        $resource->reward_service_id = $newServiceSetting->id;
                        $resource->service_type_id = $newServiceSetting->service_type_id;
                        $resource->resource_id = $serviceResource['resource_id'];
                        $resource->price_percent = $setting['percentage_of_over'];
    
                        if (!$resource->save()) {
                            throw new ModelValidationException($resource);
                        }
                    }
                }
            }

            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();
            echo '[EROR]' . $e->getMessage() . PHP_EOL;
        }
    }


    public static function calcPartner($partnerContractIds, $dateFrom, $dateTo)
    {
        $referredClients = ClientContract::find()
            ->select('c.id')
            ->joinWith('clientAccountModels as c')
            ->where(['partner_contract_id' => $partnerContractIds])
            ->column();

        if (!$referredClients) {
            throw new InvalidParamException('Клиенты не найдены');
        }

        self::_findBills($referredClients, $dateFrom, $dateTo, $partnerContractIds);
    }

    private static function _findBills($referredClients, $dateFrom, $dateTo, $partnerContractIds)
    {
        $billQuery = Bill::find()
            ->where(['client_id' => $referredClients])
            ->andWhere(['is_payed' => 1])
            ->andWhere(['>=', 'payment_date', (new \DateTime($dateFrom))->format('Y-m-d')]);

        $dateTo && $billQuery->andWhere(['<', 'payment_date', $dateTo]);

        foreach ($billQuery->each() as $bill) {
            try {
                self::processBill($bill, $partnerContractIds);
            } catch (\Exception $e) {
                echo '[ERROR] CЧЕТ ' . $bill->bill_no . ': ' . $e->getMessage() . PHP_EOL;
            }
        }
    }

    public static function makeRewardBillByPartnerId(ClientAccount $account, $contractId, \DateTime $dateFrom, \DateTime $dateTo)
    {
        $rewards = RewardBill::find()
            ->where(['partner_id' => $contractId])
            ->andWhere(['>=', 'payment_date', $dateFrom->format('Y-m-d')])
            ->andWhere(['<', 'payment_date', $dateTo->format('Y-m-d')])
            ->andWhere(['>', 'sum', 0])
            ->asArray()
            ->all();

        $dateTo->modify('-1 day');

        if (!$rewards) {
            throw new \InvalidArgumentException('Вознаграждений за указанный период не существует');
        }

        $sum = 0;
        foreach ($rewards as $reward) {
            $sum -= $reward['sum'];
        }

        $lang = $account->contragent->lang_code;

        $bill = Bill::dao()->createBill($account);
        $bill->addLine(
            \Yii::t(
                'biller',
                'partner_reward', [
                'date_range_month' => \Yii::t(
                    'biller',
                    'date_range_year',
                    [$dateFrom->getTimestamp(), $dateTo->getTimestamp()],
                    $lang)],
                $lang),
            1,
            $sum,
            BillLine::LINE_TYPE_SERVICE,
            $dateFrom->format(DateTimeZoneHelper::DATE_FORMAT),
            $dateTo->format(DateTimeZoneHelper::DATE_FORMAT),
        );
        Bill::dao()->recalcBill($bill);
    }
}


