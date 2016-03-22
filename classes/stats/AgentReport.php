<?php
namespace app\classes\stats;

use Yii;
use DateTime;
use yii\db\Expression;
use yii\db\Query;
use app\models\Business;
use app\models\ClientAccount;
use app\models\ClientContract;
use app\models\ClientContractReward;
use app\models\ClientContragent;
use app\models\Bill;
use app\models\BillLine;
use app\models\LogTarif;
use app\models\TariffVirtpbx;
use app\models\TariffVoip;
use app\models\UsageVoip;
use app\models\UsageVirtpbx;

class AgentReport
{

    /** @var DateTime $dateFrom, $dateTo */
    private static
        $dateFrom,
        $dateTo;

    public static function run($partnerId, $dateFrom, $dateTo)
    {
        $resultVoip =
        $resultVpbx = [];

        self::$dateFrom = (new DateTime($dateFrom));
        self::$dateTo = (new DateTime($dateTo));

        foreach (static::voipPartnerInfo($partnerId) as $info) {
            static::counter($info, $resultVoip, $dateFrom, $dateTo);
        }

        foreach (static::vpbxPartnerInfo($partnerId) as $info) {
            static::counter($info, $resultVpbx, $dateFrom, $dateTo);
        }

        $result = array_merge(array_values($resultVpbx), array_values($resultVoip));
        $result = array_filter($result, function($row) {
            return $row['once'] || $row['fee'] || $row['excess'];
        });

        return $result;
    }

    private static function counter($info, &$result, $dateFrom, $dateTo)
    {
        $dateFrom = strtotime($dateFrom);
        $dateTo = strtotime($dateTo);

        $billDate = strtotime($info['bill_date']);

        if (
            $dateFrom <= $billDate && $dateTo >= $billDate
                &&
            (
                $info['period_type'] === 'always'
                    ||
                $billDate < strtotime('+' . $info['period_month'] . ' month', strtotime($info['activation_dt']))
            )
        ) {
            if (!isset($result[$info['id']])) {
                $result[$info['id']] = [
                    'id' => $info['id'],
                    'name' => $info['name'],
                    'created' => $info['created'],
                    'activationDate' => $info['activation_dt'],
                    'amountIsPayed' => $info['amount_is_payed'],
                    'amount' => $info['amount'],
                    'usage' => $info['usage'],
                    'tariffName' => $info['tariff_name'],
                    'once' => 0,
                    'fee' => 0,
                    'excess' => 0,
                ];
            }

            $firstPaymentDate = strtotime($info['first_payment_date']);
            if ($firstPaymentDate <= $dateTo && $firstPaymentDate >= $dateFrom) {
                $result[$info['id']]['once'] = $info['once_only'];
            }

            switch ($info['type']) {
                case 'excess': {
                    $result[$info['id']]['excess'] += $info['percentage_of_over'] * $info['sum'] / 100;
                    break;
                }
                case 'fee': {
                    $result[$info['id']]['fee'] += $info['percentage_of_fee'] * $info['sum'] / 100;
                    break;
                }
            }
        }
    }

    private static function voipPartnerInfo($partnerId)
    {
        $dateFrom = self::$dateFrom->format('Y-m-d');
        $dateTo = self::$dateTo->format('Y-m-d');

        $query = new Query;

        $query->select([
            'clients.id',
            'client_contragent.name',
            'created' => 'DATE(clients.created)',
            'activation_dt' => 'DATE(usage_voip.activation_dt)',
            'client_contract_reward.once_only',
            'client_contract_reward.percentage_of_fee',
            'client_contract_reward.percentage_of_over',
            'client_contract_reward.period_type',
            'client_contract_reward.period_month',
            'bill_date' => 'DATE(newbills.bill_date)',
            'newbill_lines.sum',
            'tariff_name' => 'tarifs_voip.name',
            'usage' => new Expression('"voip"'),
            'type' => 'IF(newbills.bill_date > newbill_lines.date_to, "excess", "fee")',
            'amount' => '(
                SELECT SUM(sum)
                FROM newbills
                WHERE
                    newbills.client_id = clients.id
                    AND bill_date BETWEEN CAST(:dateFrom AS DATE) AND CAST(:dateTo AS DATE)
            )',
            'amount_is_payed' => '(
                SELECT SUM(sum)
                FROM newbills
                WHERE
                    newbills.client_id = clients.id
                    AND is_payed = 1
                    AND bill_date BETWEEN CAST(:dateFrom AS DATE) AND CAST(:dateTo AS DATE)
            )',
            'first_payment_date' => '(
                SELECT MIN(bill_date)
                FROM newbills
                WHERE
                    newbills.client_id = clients.id
                    AND is_payed = 1
            )',
        ]);

        $query
            ->from(ClientAccount::tableName() . ' clients')
            ->innerJoin(ClientContract::tableName() . ' client_contract', 'clients.contract_id = client_contract.id')
            ->innerJoin(ClientContragent::tableName() . ' client_contragent', 'client_contragent.id = client_contract.contragent_id')
            ->innerJoin(UsageVoip::tableName() . ' usage_voip', 'usage_voip.client = clients.client')
            ->innerJoin(LogTarif::tableName() . ' log_tarif', 'log_tarif.service = :service AND id_service = usage_voip.id')
            ->innerJoin(TariffVoip::tableName() . ' tarifs_voip', 'tarifs_voip.id = log_tarif.id_tarif')
            ->innerJoin(
                ClientContractReward::tableName() . ' client_contract_reward',
                'client_contract_reward.contract_id = client_contragent.partner_contract_id AND client_contract_reward.usage_type = :usageType'
            )
            ->innerJoin(
                BillLine::tableName() . ' newbill_lines',
                'newbill_lines.service = :service AND newbill_lines.id_service = usage_voip.id'
            )
            ->innerJoin(Bill::tableName() . ' newbills', 'newbills.bill_no = newbill_lines.bill_no');

        $query
            ->where([
                'client_contragent.partner_contract_id' => $partnerId,
                'newbills.is_payed' => 1,
            ])
            ->andWhere(['between', 'newbills.bill_date', $dateFrom, $dateTo]);

        $query->groupBy('newbill_lines.pk');

        $query->params([
            ':dateFrom' => $dateFrom,
            ':dateTo' => $dateTo,
            ':service' => UsageVoip::tableName(),
            '::billTbl' => Bill::tableName(),
            ':usageType' => 'voip',
        ]);

        return $query->all();
    }

    private static function vpbxPartnerInfo($partnerId)
    {
        $dateFrom = self::$dateFrom->format('Y-m-d');
        $dateTo = self::$dateTo->format('Y-m-d');

        $query = new Query;

        $query->select([
            'c.id',
            'cg.name',
            'created' => 'DATE(c.created)',
            'activation_dt' => 'DATE(u.activation_dt)',
            'rw.once_only',
            'rw.percentage_of_fee',
            'rw.percentage_of_over',
            'rw.period_type',
            'rw.period_month',
            'bill_date' => 'DATE(nb.bill_date)',
            'nbl.sum',
            'tariff_name' => 't.description',
            'usage' => new Expression('"vpbx"'),
            'type' => 'IF( nb.bill_date > nbl.date_to, "excess", "fee")',
            'amount' => '(
                SELECT SUM(sum)
                FROM newbills
                WHERE
                    client_id = c.id
                    AND bill_date BETWEEN CAST(:dateFrom AS DATE) AND CAST(:dateTo AS DATE)
            )',
            'amount_is_payed' => '(
                SELECT SUM(sum)
                FROM newbills
                WHERE
                    client_id = c.id
                    AND is_payed = 1
                    AND bill_date BETWEEN CAST(:dateFrom AS DATE) AND CAST(:dateTo AS DATE)
            )',
            'first_payment_date' => '(
                SELECT MIN(bill_date)
                FROM newbills
                WHERE
                    client_id = c.id
                    AND is_payed = 1
            )',
        ]);

        $query
            ->from(ClientAccount::tableName() . ' c')
            ->innerJoin(ClientContract::tableName() . ' cr', 'cr.id = c.contract_id')
            ->innerJoin(ClientContragent::tableName() . ' cg', 'cg.id = cr.contragent_id')
            ->innerJoin(UsageVirtpbx::tableName() . ' u', 'u.client = c.client')
            ->innerJoin(
                LogTarif::tableName() . ' lt',
                'lt.service = :service AND lt.id_service = u.id'
            )
            ->innerJoin(TariffVirtpbx::tableName() . ' t', 't.id = lt.id_tarif')
            ->innerJoin(
                ClientContractReward::tableName() . ' rw',
                'rw.contract_id = cg.partner_contract_id AND rw.usage_type = :usageType'
            )
            ->innerJoin(
                BillLine::tableName() . ' nbl',
                'nbl.service = :service AND nbl.id_service = u.id'
            )
            ->innerJoin(Bill::tableName() . ' nb', 'nb.bill_no = nbl.bill_no');

        $query
            ->where([
                'cg.partner_contract_id' => $partnerId,
                'nb.is_payed' => 1,
            ])
            ->andWhere(['between', 'nb.bill_date', $dateFrom, $dateTo]);

        $query->groupBy('nbl.pk');

        $query->params([
            ':dateFrom' => $dateFrom,
            ':dateTo' => $dateTo,
            ':service' => UsageVirtpbx::tableName(),
            '::billTbl' => Bill::tableName(),
            ':usageType' => 'virtpbx',
        ]);

        return $query->all();
    }

    public static function getWithoutRewardContracts()
    {
        return Yii::$app->db
            ->createCommand("
            SELECT 
                cc.id AS id,
                c.id as account_id,
                cg.name AS name
            FROM 
                (SELECT 
                    DISTINCT partner_contract_id 
                 FROM client_contragent 
                 WHERE partner_contract_id > 0
            ) p,
            clients c, client_contragent cg, client_contract cc
            LEFT JOIN client_contract_reward cr ON (cr.contract_id = cc.id)
            WHERE 
                    p.partner_contract_id = c.id
                AND c.contract_id = cc.id
                AND cc.contragent_id = cg.id
                AND cr.id is null
            ORDER BY cg.name
            ")->queryAll(\PDO::FETCH_ASSOC);
    }

    public static function getContractsWithIncorrectBP()
    {
        return Yii::$app->db
            ->createCommand("
            SELECT
                cc.id AS id,
                c.id as account_id,
                cg.name AS name
            FROM
            (
                SELECT DISTINCT 
                    partner_contract_id
                FROM client_contragent
                WHERE partner_contract_id > 0
            ) p,
            clients c, client_contragent cg, client_contract cc
        LEFT JOIN client_contract_reward cr ON (cr.contract_id = cc.id)
        WHERE
                p.partner_contract_id = c.id
            AND c.contract_id = cc.id
            AND cc.contragent_id = cg.id
            AND cr.id is null
            AND business_id != :business_id
        ORDER BY cg.name
        ", [':business_id' => Business::PARTNER])
        ->queryAll(\PDO::FETCH_ASSOC);
    }
}
