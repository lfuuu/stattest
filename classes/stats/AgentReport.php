<?php
namespace app\classes\stats;

use Yii;
use yii\db\Query;
use app\models\Business;

class AgentReport
{

    public static function run($partnerId, $dateFrom, $dateTo)
    {
        $resultVoip = [];
        $resultVpbx = [];

        foreach (static::voipPartnerInfo($partnerId) as $info) {
            static::counter($info, $resultVoip, $dateFrom, $dateTo);
        }

        foreach (static::vpbxPartnerInfo($partnerId) as $info) {
            static::counter($info, $resultVpbx, $dateFrom, $dateTo);
        }

        return array_merge(array_values($resultVpbx), array_values($resultVoip));
    }

    private static function counter($info, &$result, $dateFrom, $dateTo)
    {
        $dateFrom = strtotime($dateFrom);
        $dateTo = strtotime($dateTo);

        if($dateFrom <= strtotime($info['bill_date']) && $dateTo >= strtotime($info['bill_date'])
            && ($info['period_type'] == 'always'
                || strtotime($info['bill_date']) < strtotime("+{$info['period_month']} month", strtotime($info['activation_dt'])))
        ){
            if(!isset($result[$info['id']])){
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

            if(strtotime($info['first_payment_date']) <= $dateTo && strtotime($info['first_payment_date']) >= $dateFrom){
                $result[$info['id']]['once'] = $info['once_only'];
            }

            if($info['type'] == 'excess'){
                $result[$info['id']]['excess'] += $info['percentage_of_over'] * $info['sum'] / 100;
            } elseif($info['type'] == 'fee'){
                $result[$info['id']]['fee'] += $info['percentage_of_fee'] * $info['sum'] / 100;
            }
        }
    }

    private static function voipPartnerInfo($partnerId)
    {
        $query = (new Query())
            ->select([
                'clients.id',
                'client_contragent.name',
                'DATE(clients.created) AS created',
                'DATE(usage_voip.activation_dt) AS activation_dt',
                'client_contract_reward.once_only',
                'client_contract_reward.percentage_of_fee',
                'client_contract_reward.percentage_of_over',
                'client_contract_reward.period_type',
                'client_contract_reward.period_month',
                'DATE(newbills.bill_date) AS bill_date',
                'newbill_lines.sum',
                'tarifs_voip.name AS tariff_name',
                '\'voip\' AS `usage`',

                'IF( newbills.bill_date > newbill_lines.date_to, \'excess\', \'fee\' ) AS `type`',

                '(SELECT SUM(sum) FROM newbills WHERE newbills.client_id = clients.id) AS `amount`',
                '(SELECT SUM(sum) FROM newbills WHERE newbills.client_id = clients.id AND is_payed = 1) AS `amount_is_payed`',
                '(SELECT MIN(bill_date) FROM newbills WHERE newbills.client_id = clients.id AND is_payed = 1 ) AS `first_payment_date`',
            ])
            ->from('clients')
            ->innerJoin('client_contract', 'clients.contract_id = client_contract.id')
            ->innerJoin('client_contragent', 'client_contragent.id = client_contract.contragent_id')
            ->innerJoin('usage_voip', 'usage_voip.client = clients.client')
            ->innerJoin('log_tarif', 'log_tarif.service = \'usage_voip\' AND id_service = usage_voip.id')
            ->innerJoin('tarifs_voip', 'tarifs_voip.id = log_tarif.id_tarif')
            ->innerJoin('client_contract_reward',
                'client_contract_reward.contract_id = client_contragent.partner_contract_id AND client_contract_reward.usage_type = \'voip\''
            )
            ->innerJoin('newbill_lines', 'newbill_lines.service = \'usage_voip\' AND newbill_lines.id_service = usage_voip.id')
            ->innerJoin('newbills', 'newbills.bill_no = newbill_lines.bill_no')

            ->where('newbills.is_payed = 1')
            ->andWhere('client_contragent.partner_contract_id = :partnerId', [
                ':partnerId' => $partnerId,
            ])->groupBy('newbill_lines.pk');

        return $query->createCommand()->queryAll(\PDO::FETCH_ASSOC);
    }

    private static function vpbxPartnerInfo($partnerId)
    {
        return Yii::$app->db
            ->createCommand("
                    SELECT 
                       c.id, 
                       cg.name, 
                       DATE(c.created) AS created, 
                       DATE(u.activation_dt) AS activation_dt,
                       rw.once_only, rw.percentage_of_fee, rw.percentage_of_over, rw.period_type, rw.period_month,
                       DATE(nb.bill_date) AS bill_date, nbl.sum, t.description AS tariff_name, 'vpbx' AS `usage`,
                       IF( nb.bill_date > nbl.date_to, 'excess', 'fee' ) AS `type`,
                       (SELECT SUM(sum) FROM newbills WHERE client_id = c.id) AS `amount`,
                       (SELECT SUM(sum) FROM newbills WHERE client_id = c.id AND is_payed = 1) AS `amount_is_payed`,
                       (SELECT MIN(bill_date) FROM newbills WHERE client_id = c.id AND is_payed = 1) AS `first_payment_date`
                   FROM clients c
                   INNER JOIN client_contract cr ON cr.id = c.contract_id
                   INNER JOIN client_contragent cg ON cg.id = cr.contragent_id
                   INNER JOIN usage_virtpbx u ON u.client = c.client
                   INNER JOIN `log_tarif` lt ON lt.service = 'usage_voip' AND id_service = u.id 
                   INNER JOIN tarifs_virtpbx t ON t.id = lt.id_tarif
                   INNER JOIN client_contract_reward rw ON rw.contract_id = cg.partner_contract_id AND rw.usage_type = 'virtpbx'
                   INNER JOIN newbill_lines nbl ON nbl.service = 'usage_virtpbx' AND nbl.id_service = u.id
                   INNER JOIN newbills nb ON nb.bill_no = nbl.bill_no

                   WHERE cg.partner_contract_id = :partnerId AND nb.is_payed = 1
                   GROUP BY nbl.pk
        ", [':partnerId' => $partnerId])
            ->queryAll(\PDO::FETCH_ASSOC);
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
