<?php
namespace app\classes\stats;

use app\helpers\DateTimeZoneHelper;
use Yii;
use DateTime;
use yii\db\Expression;
use yii\db\Query;
use app\models\Bill;
use app\models\Business;
use app\models\Transaction;
use app\models\ClientAccount;
use app\models\ClientContract;
use app\models\ClientContractReward;
use app\models\ClientContragent;
use app\models\UsageVoip;
use app\models\UsageVirtpbx;

class AgentReport
{

    /** @var DateTime $dateFrom , $dateTo */
    private
        $dateFrom,
        $dateTo;

    public $summary = [];

    /**
     * @param int $partnerId
     * @param string $dateFrom
     * @param string $dateTo
     * @return array
     */
    public function run($partnerId, $dateFrom, $dateTo)
    {
        $reportVoip =
        $reportVirpbx = [];

        $this->dateFrom = (new DateTime($dateFrom));
        $this->dateTo = (new DateTime($dateTo));

        foreach ($this->voipPartnerInfo($partnerId) as $info) {
            $this->counter($info, $reportVoip);
        }

        foreach ($this->vpbxPartnerInfo($partnerId) as $info) {
            $this->counter($info, $reportVirpbx);
        }

        $report = array_merge(array_values($reportVoip), array_values($reportVirpbx));
        $report = array_filter($report, function ($row) {
            return $row['amount'] > 0 || $row['amount_payed'] > 0;
        });

        return $report;
    }

    /**
     * @param array $row
     * @param array $result
     */
    private function counter($row, &$result)
    {
        $billDate = (new DateTime($row['bill_date']));

        if ($this->dateFrom <= $billDate && $this->dateTo >= $billDate) {
            $blockKey = $row['client_id'];

            $rewards = [];

            if (!array_key_exists($row['partner_contract_id'], $rewards)) {
                $rewardsQuery =
                    ClientContractReward::find()
                        ->where([
                            'usage_type' => $row['usage_type'],
                            'contract_id' => $row['partner_contract_id'],
                        ])
                        ->orderBy(['actual_from' => SORT_ASC]);
                $rewardsQueryTest = clone $rewardsQuery;
                $rewardsActualExpression = new Expression(
                    'actual_from >= CAST(:billDate AS DATE)',
                    ['billDate' => $row['bill_date']]
                );

                if ($rewardsQueryTest->andWhere($rewardsActualExpression)->count()) {
                    $rewards[$row['partner_contract_id']] =
                        $rewardsQuery
                            ->andWhere($rewardsActualExpression)
                            ->one();
                } else {
                    $rewards[$row['partner_contract_id']] = $rewardsQuery->one();
                }
            }

            $rewardSettings = $rewards[$row['partner_contract_id']];
            $dateOffset = (new DateTime($row['activation_dt']))->modify('+' . $rewardSettings['period_month'] . ' month');

            if ($rewardSettings['period_type'] !== 'always' && $billDate > $dateOffset) {
                return;
            }

            if (!isset($result[$blockKey])) {
                $result[$blockKey] = [
                    'client_id' => $row['client_id'],
                    'contragent_name' => $row['contragent_name'],
                    'client_created' => $row['client_created'],
                    'activation_datetime' => $row['usage_activation_dt'],
                    'amount' => $row['amount'],
                    'amount_payed' => $row['amount_is_payed'],
                    'once' => 0,
                    'fee' => 0,
                    'excess' => 0,
                    'details' => [],
                ];
            }

            $result[$blockKey]['details'][] = $row;

            $firstPaymentDate = (new DateTime($row['first_payment_date']));
            if ($firstPaymentDate <= $this->dateTo && $firstPaymentDate >= $this->dateFrom) {
                $result[$blockKey]['once'] = $rewardSettings['once_only'];
                $this->summary['once'] += $rewardSettings['once_only'];
            }

            $result[$blockKey]['amount'] += $row['sum'];
            $this->summary['amount'] += $row['sum'];

            if ($row['is_payed'] == 1) {
                $result[$blockKey]['amount_payed'] += $row['sum'];
                $this->summary['amount_payed'] += $row['sum'];

                switch ($row['transaction_type']) {
                    case Transaction::TYPE_RESOURCE: {
                        $transactionSummaryValue = $rewardSettings['percentage_of_over'] * $row['sum'] / 100;
                        $result[$blockKey]['excess'] += $transactionSummaryValue;
                        $this->summary['excess'] += $transactionSummaryValue;
                        break;
                    }
                    case Transaction::TYPE_PERIODICAL: {
                        $transactionSummaryValue = $rewardSettings['percentage_of_fee'] * $row['sum'] / 100;
                        $result[$blockKey]['fee'] += $transactionSummaryValue;
                        $this->summary['fee'] += $transactionSummaryValue;
                        break;
                    }
                }
            }
        }
    }

    /**
     * @param int $partnerId
     * @return array
     */
    private function voipPartnerInfo($partnerId)
    {
        $dateFrom = $this->dateFrom->format(DateTimeZoneHelper::DATE_FORMAT);
        $dateTo = $this->dateTo->format(DateTimeZoneHelper::DATE_FORMAT);

        $query = new Query;

        $query->select([
            'client_id' => 'client.id',
            'partner_contract_id' => 'contract.partner_contract_id',
            'contragent_name' => 'contragent.name',
            'client_created' => 'DATE(client.created)',
            'usage_type' => new Expression('"usage_voip"'),
            'usage_id' => 'usage.id',
            'bill_date' => 'DATE(bills.bill_date)',
            'transaction.name',
            'transaction_type' => new Expression("IFNULL(transaction.transaction_type, '" . Transaction::TYPE_PERIODICAL . "')"),
            'transaction.sum',
            'bills.is_payed',
            'bills.bill_no',
            'first_payment_date' => '(
                SELECT MIN(bill_date)
                FROM newbills
                WHERE
                    newbills.client_id = client.id
                    AND biller_version = ' . ClientAccount::VERSION_BILLER_USAGE . '
                    AND is_payed = 1
            )',
        ]);

        $query
            ->from(['client' => ClientAccount::tableName()])
            ->innerJoin(['contract' => ClientContract::tableName()], 'client.contract_id = contract.id')
            ->innerJoin(['contragent' => ClientContragent::tableName()], 'contragent.id = contract.contragent_id')
            ->innerJoin(['usage' => UsageVoip::tableName()], 'usage.client = client.client')
            ->innerJoin(
                ['transaction' => Transaction::tableName()],
                'transaction.service_type = :service AND transaction.service_id = usage.id'
            )
            ->innerJoin(['bills' => Bill::tableName()], 'bills.id = transaction.bill_id');

        $query
            ->where([
                'contract.partner_contract_id' => $partnerId,
                'transaction.deleted' => 0,
            ])
            ->andWhere(['BETWEEN', 'bills.bill_date', $dateFrom, $dateTo])
            ->andWhere([
                'OR',
                ['IN', 'transaction.transaction_type', [Transaction::TYPE_PERIODICAL, Transaction::TYPE_RESOURCE]],
                ['IS', 'transaction.transaction_type', null]
            ]);

        $query->params([
            ':service' => UsageVoip::tableName(),
        ]);

        return $query->each();
    }

    /**
     * @param int $partnerId
     * @return array
     */
    private function vpbxPartnerInfo($partnerId)
    {
        $dateFrom = $this->dateFrom->format(DateTimeZoneHelper::DATE_FORMAT);
        $dateTo = $this->dateTo->format(DateTimeZoneHelper::DATE_FORMAT);

        $query = new Query;

        $query->select([
            'client_id' => 'client.id',
            'partner_contract_id' => 'contract.partner_contract_id',
            'contragent_name' => 'contragent.name',
            'client_created' => 'DATE(client.created)',
            'usage_type' => new Expression('"usage_virtpbx"'),
            'usage_id' => 'usage.id',
            'bill_date' => 'DATE(bills.bill_date)',
            'transaction.name',
            'transaction_type' => new Expression("IFNULL(transaction.transaction_type, '" . Transaction::TYPE_PERIODICAL . "')"),
            'transaction.sum',
            'bills.is_payed',
            'bills.bill_no',
            'first_payment_date' => '(
                SELECT MIN(bill_date)
                FROM newbills
                WHERE
                    newbills.client_id = client.id
                    AND biller_version = ' . ClientAccount::VERSION_BILLER_USAGE . '
                    AND is_payed = 1
            )',
        ]);

        $query
            ->from(['client' => ClientAccount::tableName()])
            ->innerJoin(['contract' => ClientContract::tableName()], 'client.contract_id = contract.id')
            ->innerJoin(['contragent' => ClientContragent::tableName()], 'contragent.id = contract.contragent_id')
            ->innerJoin(['usage' => UsageVirtpbx::tableName()], 'usage.client = client.client')
            ->innerJoin(
                ['transaction' => Transaction::tableName()],
                'transaction.service_type = :service AND transaction.service_id = usage.id'
            )
            ->innerJoin(['bills' => Bill::tableName()], 'bills.id = transaction.bill_id');

        $query
            ->where([
                'contract.partner_contract_id' => $partnerId,
                'transaction.deleted' => 0,
            ])
            ->andWhere(['BETWEEN', 'bills.bill_date', $dateFrom, $dateTo])
            ->andWhere([
                'OR',
                ['IN', 'transaction.transaction_type', [Transaction::TYPE_PERIODICAL, Transaction::TYPE_RESOURCE]],
                ['IS', 'transaction.transaction_type', null]
            ]);

        $query->params([
            ':service' => UsageVirtpbx::tableName(),
        ]);

        return $query->each();
    }

    /**
     * @return array
     */
    public function getWithoutRewardContracts()
    {
        $query = new Query;

        $query->select([
            'contract_id' => 'cc.id',
            'contragent_name' => 'cg.name',
        ]);

        $query
            ->from([
                'p' => new Expression('(
                    SELECT DISTINCT `partner_contract_id`
                    FROM `client_contract`
                    WHERE `partner_contract_id` > 0
                )'),
                'cg' => ClientContragent::tableName(),
                'cc' => ClientContract::tableName(),
            ])
            ->leftJoin(['cr' => ClientContractReward::tableName(),], 'cr.contract_id = cc.id');

        $query
            ->where('p.partner_contract_id = cc.id')
            ->andWhere('cc.contragent_id = cg.id')
            ->andWhere(['IS', 'cr.id', new Expression('NULL')]);

        $query->orderBy([
            'cg.name' => SORT_ASC
        ]);

        return $query->all();
    }

    /**
     * @return array
     */
    public function getContractsWithIncorrectBP()
    {
        $query = new Query;

        $query->select([
            'contract_id' => 'cc.id',
            'contragent_name' => 'cg.name',
        ]);

        $query
            ->from([
                'p' => new Expression('(
                    SELECT DISTINCT `partner_contract_id`
                    FROM `client_contract`
                    WHERE `partner_contract_id` > 0
                )'),
                'cg' => ClientContragent::tableName(),
                'cc' => ClientContract::tableName(),
            ])
            ->leftJoin(['cr' => ClientContractReward::tableName(),], 'cr.contract_id = cc.id');

        $query
            ->where('p.partner_contract_id = cc.id')
            ->andWhere('cc.contragent_id = cg.id')
            ->andWhere(['IS', 'cr.id', new Expression('NULL')])
            ->andWhere(['!=', 'business_id', Business::PARTNER]);

        $query->orderBy([
            'cg.name' => SORT_ASC,
        ]);

        return $query->all();
    }
}
