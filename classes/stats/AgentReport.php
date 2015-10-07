<?php
namespace app\classes\stats;

use Yii;

class AgentReport
{

    public function vpbx($partnerId, $dateFrom, $dateTo)
    {
        $lines = Yii::$app
            ->db
            ->createCommand("
                SELECT c.id, cg.name, c.created, DATE(u.activation_dt), DATE(u.expire_dt)
                  rw.once_only, rw.percentage_of_fee, rw.percentage_of_ower, rw.period_type. rw.period_month,
                  p.payment_date, p.sum
                  FROM clients c
                  INNER JOIN client_contract cr ON cr.id = c.contract_id
                  INNER JOIN client_contragent cg ON cg.id = cr.contragent_id
                  INNER JOIN usage_virtpbx u ON u.client = c.client
                  INNER JOIN tarifs_virtpbx t ON t.id = u.tarif_id
                  INNER JOIN client_contract_reward rw ON rw.contract_id = cr.id AND rw.uage_type = 'virtpbx'
                  INNER JOIN newbill_lines b ON b.service = 'usage_virtpbx' AND b.id_service = u.id
                  WHERE cr.partner_id = :partnerId AND u.status = 'working'
                    AND :dateFrom >= u.activation_dt
                    AND :dateTo <= u.expire_dt
        ", [':partnerId' => $partnerId, ':dateFrom' => $dateFrom, ':dateTo' => $dateTo])
        ->queryAll(\PDO::FETCH_ASSOC);
        foreach($lines as $line){

        }
    }


}
