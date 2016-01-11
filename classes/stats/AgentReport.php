<?php
namespace app\classes\stats;

use Yii;

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
        return Yii::$app->db
            ->createCommand("
                SELECT c.id, cg.name, DATE(c.created) AS created, DATE(u.activation_dt) AS activation_dt,
                       rw.once_only, rw.percentage_of_fee, rw.percentage_of_over, rw.period_type, rw.period_month,
                       DATE(nb.bill_date) AS bill_date, nb.sum, t.name AS tariff_name, 'voip' AS `usage`,
                       IF( nb.bill_date > nbl.date_to, 'excess', 'fee' ) AS `type`,
                       (SELECT SUM(sum) FROM newbills WHERE client_id = c.id) AS `amount`,
                       (SELECT SUM(sum) FROM newbills WHERE client_id = c.id AND is_payed = 1) AS `amount_is_payed`,
                       (SELECT bill_date FROM newbills WHERE client_id = c.id AND is_payed = 1 LIMIT 1) AS `first_payment_date`
                   FROM clients c
                   INNER JOIN client_contract cr ON cr.id = c.contract_id
                   INNER JOIN client_contragent cg ON cg.id = cr.contragent_id
                   INNER JOIN usage_voip u ON u.client = c.client
                   INNER JOIN log_tarif lt ON lt.service = 'usage_voip' AND id_service = u.id
                   INNER JOIN tarifs_voip t ON t.id = lt.id_tarif
                   INNER JOIN client_contract_reward rw ON rw.contract_id = cg.partner_contract_id AND rw.usage_type = 'voip'
                   INNER JOIN newbill_lines nbl ON nbl.service = 'usage_voip' AND nbl.id_service = u.id
                   INNER JOIN newbills nb ON nb.bill_no = nbl.bill_no

                   WHERE /* cg.partner_contract_id = :partnerId AND */ nb.is_payed = 1
        ", [':partnerId' => $partnerId])
            ->queryAll(\PDO::FETCH_ASSOC);
    }

    private static function vpbxPartnerInfo($partnerId)
    {
        return Yii::$app->db
            ->createCommand("
                SELECT c.id, cg.name, DATE(c.created) AS created, DATE(u.activation_dt) AS activation_dt,
                       rw.once_only, rw.percentage_of_fee, rw.percentage_of_over, rw.period_type, rw.period_month,
                       DATE(nb.bill_date) AS bill_date, nb.sum, t.description AS tariff_name, 'vpbx' AS `usage`,
                       IF( nb.bill_date > nbl.date_to, 'excess', 'fee' ) AS `type`,
                       (SELECT SUM(sum) FROM newbills WHERE client_id = c.id) AS `amount`,
                       (SELECT SUM(sum) FROM newbills WHERE client_id = c.id AND is_payed = 1) AS `amount_is_payed`,
                       (SELECT bill_date FROM newbills WHERE client_id = c.id AND is_payed = 1 LIMIT 1) AS `first_payment_date`
                   FROM clients c
                   INNER JOIN client_contract cr ON cr.id = c.contract_id
                   INNER JOIN client_contragent cg ON cg.id = cr.contragent_id
                   INNER JOIN usage_virtpbx u ON u.client = c.client
                   INNER JOIN tarifs_virtpbx t ON t.id = u.tarif_id
                   INNER JOIN client_contract_reward rw ON rw.contract_id = cg.partner_contract_id AND rw.usage_type = 'virtpbx'
                   INNER JOIN newbill_lines nbl ON nbl.service = 'usage_virtpbx' AND nbl.id_service = u.id
                   INNER JOIN newbills nb ON nb.bill_no = nbl.bill_no

                   WHERE /* cg.partner_contract_id = :partnerId AND */ nb.is_payed = 1
        ", [':partnerId' => $partnerId])
            ->queryAll(\PDO::FETCH_ASSOC);
    }
}
