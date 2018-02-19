<?php

namespace app\classes\partners;

use app\classes\Assert;
use app\classes\partners\handler\AHandler;
use app\classes\partners\handler\CallChatHandler;
use app\classes\partners\handler\TrunkHandler;
use app\classes\partners\handler\VirtpbxHandler;
use app\classes\partners\handler\VoipHandler;
use app\classes\partners\rewards\Reward;
use app\dao\BillDao;
use app\exceptions\ModelValidationException;
use app\models\Bill;
use app\models\BillLine;
use app\models\ClientAccount;
use app\models\ClientContractReward;
use app\models\PartnerRewards;
use app\models\Transaction;
use yii\db\Expression;
use yii\db\Query;

abstract class RewardCalculate
{
    /** @var string[] */
    public static $services = [
        Transaction::SERVICE_VOIP => VoipHandler::class,
        Transaction::SERVICE_VIRTPBX => VirtpbxHandler::class,
        Transaction::SERVICE_CALL_CHAT => CallChatHandler::class,
        Transaction::SERVICE_TRUNK => TrunkHandler::class,
    ];

    /**
     * @param int $clientAccountId
     * @param int $billId
     * @param string $createdAt
     * @throws \yii\base\Exception
     */
    public static function run($clientAccountId, $billId, $createdAt)
    {
        /** @var ClientAccount $clientAccount */
        $clientAccount = ClientAccount::findOne(['id' => $clientAccountId]);
        Assert::isObject($clientAccount);
        Assert::isNotEmpty($clientAccount->contract->contragent->partner_contract_id);

        $bill = Bill::findOne(['id' => $billId]);
        Assert::isObject($bill, 'Bill #' . $billId . ' not found');

        // Список используемых настроек вознаграждений
        $contractRewards = (new Query)
            ->select([
                'usage_type',
                'once_only',
                'percentage_once_only',
                'percentage_of_fee',
                'percentage_of_over',
                'percentage_of_margin',
                'period_type',
                'period_month',
            ])
            ->from([
                'rewards' => (new Query)
                    ->from(ClientContractReward::tableName())
                    ->where(['contract_id' => $clientAccount->contract->contragent->partner_contract_id])
                    ->andWhere(['<', 'actual_from', new Expression('CAST(:createdAt AS DATE)', ['createdAt' => $createdAt])])
                    ->orderBy(['actual_from' => SORT_DESC])
            ])
            ->groupBy('usage_type')
            ->indexBy('usage_type')
            ->all();

        foreach ($bill->lines as $line) {

            if ($line->sum < 0) {
                continue;
            }

            if ($line->service == BillDao::UU_SERVICE) {
                // для УУ определить соответствующий тип неуниверсальной услуги
                $accountTariff = $line->accountTariff;
                $serviceType = $accountTariff ? $accountTariff->serviceType : null;
                $service = $serviceType ? $serviceType->getUsageName() : null;
            } else {
                $service = $line->service;
            }

            if (!array_key_exists($service, $contractRewards)) {
                // В настройках вознаграждения нет данного типа услуги
                continue;
            }

            $rewardsSettingsByType = $contractRewards[$service];

            if (
                $rewardsSettingsByType['period_type'] === ClientContractReward::PERIOD_MONTH
                && $rewardsSettingsByType['period_month'] < BillLine::find()
                    ->where([
                        'service' => $service,
                        'id_service' => $line->id_service,
                    ])
                    ->andWhere(['<', 'date_to', $line->date_to])
                    ->count()
            ) {
                // Период выплат ограничен и их кол-во не превышает настроенное
                continue;
            }

            // Определение обработчика начисления вознаграждения
            $rewardClassName = self::$services[$service];
            /** @var AHandler $rewardsHandler */
            $rewardsHandler = new $rewardClassName([
                'clientAccountVersion' => $clientAccount->account_version,
            ]);

            $serviceObj = $rewardsHandler->getService($line->id_service);
            if ($rewardsHandler->isExcludeService($serviceObj)) {
                // Услуга исключена из вознаграждений
                continue;
            }

            $reward = PartnerRewards::findOne(['bill_id' => $bill, 'line_pk' => $line->pk]);
            if (is_null($reward)) {
                $reward = new PartnerRewards;
                $reward->bill_id = $bill->id;
                $reward->line_pk = $line->pk;
            }

            $reward->created_at = $createdAt;

            foreach ($rewardsHandler->getAvailableRewards() as $rewardClass) {
                /** @var Reward $rewardClass */
                $rewardClass::calculate($reward, $line, $rewardsSettingsByType);
            }

            if (!$reward->save()) {
                throw new ModelValidationException($reward);
            }
        }
    }

}