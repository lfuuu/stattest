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
        $dateOffset = (new DateTime($row['activation_dt']))->modify('+' . $row['period_month'] . ' month');

        if (
            $this->dateFrom <= $billDate && $this->dateTo >= $billDate
            &&
            (
                $row['period_type'] === 'always'
                ||
                $billDate < $dateOffset
            )
        ) {
            $blockKey = $row['client_id'];

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
                $result[$blockKey]['once'] = $row['once_only'];
                $this->summary['once'] += $row['once_only'];
            }

            $result[$blockKey]['amount'] += $row['sum'];
            $this->summary['amount'] += $row['sum'];

            if ($row['is_payed'] == 1) {
                $transactionSummaryValue = 0;
                $result[$blockKey]['amount_payed'] += $row['sum'];
                $this->summary['amount_payed'] += $row['sum'];

                switch ($row['transaction_type']) {
                    case Transaction::TYPE_RESOURCE: {
                        $transactionSummaryValue = $row['percentage_of_over'] * $row['sum'] / 100;
                        $result[$blockKey]['excess'] += $transactionSummaryValue;
                        $this->summary['excess'] += $transactionSummaryValue;
                        break;
                    }
                    case Transaction::TYPE_PERIODICAL: {
                        $transactionSummaryValue = $row['percentage_of_fee'] * $row['sum'] / 100;
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
            'contragent_name' => 'contragent.name',
            'client_created' => 'DATE(client.created)',
            'usage_type' => new Expression('"voip"'),
            'usage_id' => 'usage.id',
            'rewards.once_only',
            'rewards.percentage_of_fee',
            'rewards.percentage_of_over',
            'rewards.period_type',
            'rewards.period_month',
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
                ['rewards' => ClientContractReward::tableName()],
                'rewards.contract_id = contragent.partner_contract_id AND rewards.usage_type = :usageType'
            )
            ->innerJoin(
                ['transaction' => Transaction::tableName()],
                'transaction.service_type = :service AND transaction.service_id = usage.id'
            )
            ->innerJoin(['bills' => Bill::tableName()], 'bills.id = transaction.bill_id');

        $query
            ->where([
                'contragent.partner_contract_id' => $partnerId,
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
            ':usageType' => 'usage_voip',
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
            'contragent_name' => 'contragent.name',
            'client_created' => 'DATE(client.created)',
            'usage_type' => new Expression('"vpbx"'),
            'usage_id' => 'usage.id',
            'rewards.once_only',
            'rewards.percentage_of_fee',
            'rewards.percentage_of_over',
            'rewards.period_type',
            'rewards.period_month',
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
                ['rewards' => ClientContractReward::tableName()],
                'rewards.contract_id = contragent.partner_contract_id AND rewards.usage_type = :usageType'
            )
            ->innerJoin(
                ['transaction' => Transaction::tableName()],
                'transaction.service_type = :service AND transaction.service_id = usage.id'
            )
            ->innerJoin(['bills' => Bill::tableName()], 'bills.id = transaction.bill_id');

        $query
            ->where([
                'contragent.partner_contract_id' => $partnerId,
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
            ':usageType' => 'usage_virtpbx',
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
                    FROM `client_contragent`
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
                    FROM `client_contragent`
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
