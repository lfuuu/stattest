<?php

namespace app\classes\partners;

use yii\db\Expression;
use yii\db\Query;
use app\classes\Assert;
use app\classes\partners\rewards\Reward;
use app\models\ClientAccount;
use app\models\PartnerRewards;
use app\models\Transaction;
use app\models\ClientContractReward;
use app\models\Bill;

abstract class RewardCalculate
{

    public static $services = [
        Transaction::SERVICE_VOIP => VoipRewards::class,
        Transaction::SERVICE_VIRTPBX => VirtpbxRewards::class,
        Transaction::SERVICE_CALL_CHAT => CallChatRewards::class,
        Transaction::SERVICE_TRUNK => TrunkRewards::class,
    ];

    /**
     * @param int $clientAccountId
     * @param int $billId
     * @param string $createdAt
     * @throws \yii\base\Exception
     */
    public static function run($clientAccountId, $billId, $createdAt)
    {
        $clientAccount = ClientAccount::findOne(['id' => $clientAccountId]);
        Assert::isObject($clientAccount);
        Assert::isNotEmpty($clientAccount->contract->contragent->partner_contract_id);

        $bill = Bill::findOne(['id' => $billId]);
        Assert::isObject($bill);

        $contractRewards =
            (new Query)
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
            if (!array_key_exists($line->service, $contractRewards)) {
                continue;
            }

            // Определение обработчика начисления вознаграждения
            $rewardsClass = self::$services[$line->service];

            $reward = PartnerRewards::findOne(['bill_id' => $bill, 'line_pk' => $line->pk]);
            if (is_null($reward)) {
                $reward = new PartnerRewards;
                $reward->bill_id = $bill->id;
                $reward->line_pk = $line->pk;
            }

            $reward->created_at = $createdAt;

            foreach ($rewardsClass::$availableRewards as $rewardClass) {
                /** @var Reward $rewardClass*/
                $rewardClass::calculate($reward, $line, $contractRewards[$line->service]);
            }

            if (!$reward->save()) {
                throw new \LogicException(implode('', $reward->getFirstErrors()));
            }
        }
    }

}