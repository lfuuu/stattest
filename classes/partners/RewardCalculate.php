<?php

namespace app\classes\partners;

use app\classes\Assert;
use app\classes\HandlerLogger;
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
use app\modules\uu\models\ServiceType;
use yii\db\Expression;

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
     * @param ClientAccount|int $clientAccount
     * @param Bill|int $bill
     * @param string $createdAt
     * @throws \yii\base\Exception
     */
    public static function run($clientAccount, $bill, $createdAt)
    {
        if (!($clientAccount instanceof ClientAccount)) {
            $id = $clientAccount;
            $clientAccount = ClientAccount::findOne(['id' => $id]);
        }
        Assert::isObject($clientAccount);
        if (!($bill instanceof Bill)) {
            $id = $bill;
            $bill = Bill::findOne(['id' => $id]);
        }
        Assert::isObject($bill);
        // Получение partner_contract_id для дальнейшего поиска партнерской настройки
        $contract = $clientAccount->contract;
        $partnerContractId = $contract->partner_contract_id ?: $contract->contragent->partner_contract_id;
        if (!$partnerContractId) {
            HandlerLogger::me()->add(sprintf('pci_%s ', $contract->id));
            return;
        }
        // Список используемых настроек вознаграждений, которые группируются по типу, из которого берется самое последнее вознаграждение
        $contractRewards = ClientContractReward::find()
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
            ->innerJoin([
                'groupped' => ClientContractReward::find()
                    ->select(['id' => new Expression('MAX(id)')])
                    ->where(['contract_id' => $partnerContractId])
                    ->andWhere(['<', 'actual_from', $createdAt])
                    ->groupBy('usage_type')
            ], 'groupped.id = ' . ClientContractReward::tableName() . '.id')
            ->indexBy('usage_type')
            ->asArray()
            ->all();
        if (!$contractRewards) {
            return;
        }
        // Получение строчек текущего счета
        $linesQuery = BillLine::find()
            ->where([
                'bill_no' => $bill->bill_no,
                'type' => BillLine::LINE_TYPE_SERVICE,
            ])
            ->andWhere(['>', 'sum', 0])
            ->andWhere(['IS NOT', 'service', null]);
        foreach ($linesQuery->each() as $line) {
            // Для универсальной услуги получаем тип неуниверсальной услуги
            if ($line->service == BillDao::UU_SERVICE) {
                $accountTariff = $line->accountTariff;
                /** @var ServiceType $serviceType */
                $serviceType = $accountTariff ? $accountTariff->serviceType : null;
                $service = $serviceType ? $serviceType->getUsageName() : null;
            } else {
                $service = $line->service;
            }
            // В настройках вознаграждения нет данного типа услуги
            if (!isset($contractRewards[$service])) {
                continue;
            }
            $rewardsSettingsByType = $contractRewards[$service];
            // Если периодом услуги является месяц, то нужно проверить пролонгацию
            if ($rewardsSettingsByType['period_type'] === ClientContractReward::PERIOD_MONTH) {
                // Пролонгация - месяц добавление настройки к остальной пролонгации
                $prolongation = (int)$rewardsSettingsByType['period_month'] + 1;
                // Проверка попадания строчки счета в пролонгацию для расчета вознаграждения
                $billLineTableName = BillLine::tableName();
                $isCanReward = BillLine::getDb()->createCommand("
                    SELECT (TIMESTAMPDIFF(
                        MONTH,
                        COALESCE (
                            (
                                SELECT MIN(date_from) date_from
                                FROM {$billLineTableName}
                                WHERE service = '{$service}' AND id_service = {$line->id_service}
                            ),
                            '{$line->date_from}'
                        ),
                        '{$line->date_from}'
                    ) >= {$prolongation} ) val;
                ")
                    ->queryScalar();
                // Период выплат ограничен и их количество не превышает настроенное
                if ($isCanReward) {
                    continue;
                }
            }
            // Определение обработчика начисления вознаграждения
            $rewardClassName = self::$services[$service];
            /** @var AHandler $rewardsHandler */
            $rewardsHandler = new $rewardClassName([
                'clientAccountVersion' => $clientAccount->account_version,
            ]);
            // Получение обработчиков для строчки счета (Разовое, % от подключения, % от абонентской платы, % от превышения, % от маржи)
            $serviceObj = $rewardsHandler->getService($line->id_service);
            // Услуга исключена из вознаграждений
            if ($rewardsHandler->isExcludeService($serviceObj)) {
                continue;
            }
            // Попытка найти партнерское вознаграждение
            if (!($reward = PartnerRewards::findOne(['bill_id' => $bill, 'line_pk' => $line->pk]))) {
                $reward = new PartnerRewards;
                $reward->bill_id = $bill->id;
                $reward->line_pk = $line->pk;
            }
            $reward->created_at = $createdAt;
            // Выполнение рассчета прикрепленными обработчиками
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