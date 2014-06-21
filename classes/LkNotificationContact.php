<?php 

class LkNotificationContact
{
    public static function getList()
    {
        $res = ClientContact::find_by_sql("
            SELECT
                cc.id,
                cc.type,
                cc.data,
                ns.min_balance AS is_min_balance,
                ns.day_limit AS is_day_limit,
                cs.is_min_balance_sent,
                cs.is_day_limit_sent,
                cs.client_id,
                cs.min_balance,
                cs.day_limit,
                c.balance,
                c.credit,
                0 AS day_sum
            FROM
                client_contacts cc
            LEFT JOIN lk_notice_settings ns ON cc.id=ns.client_contact_id
            LEFT JOIN lk_client_settings cs ON cs.client_id = ns.client_id
            LEFT JOIN clients c ON c.id=cc.client_id
            WHERE
                ns.status='working' AND
                c.status in ('work','connecting','testing') AND
                (ns.min_balance='1' OR ns.day_limit='1')
            ");

        $BC = array();
        foreach ($res as &$r) 
        {
            if (!isset($BC[$r->client_id]))
                $BC[$r->client_id] = ClientCS::getBillingCounters($r->client_id);

            $r->day_sum = $BC[$r->client_id]['amount_day_sum'];

            if ($r->credit >= 0) {
                $r->balance -= $BC[$r->client_id]['amount_sum'];
            }
        }

        return $res;
    }

    public static function getListForPayment($client_id)
    {
        $res = ClientContact::find_by_sql("
            SELECT
                cc.id,
                cc.type,
                cc.data,
                cs.client_id,
                c.balance
            FROM
                client_contacts cc
            LEFT JOIN lk_notice_settings ns ON cc.id=ns.client_contact_id
            LEFT JOIN lk_client_settings cs ON cs.client_id = ns.client_id
            LEFT JOIN clients c ON c.id=cc.client_id
            WHERE
                c.id=" . $client_id . " AND
                ns.status='working' AND
                ns.add_pay_notif='1' 
            ");

        return $res;
    }
    
    public static function createBalanceNotifacation($clientId, $paymentId)
    {
        $pay = Payment::find(array("conditions" => array("client_id" => $clientId, "id" => $paymentId)));

        if (!$pay) 
            return false;

        $Contacts = self::getListForPayment($clientId);
        if ($Contacts) {
            foreach ($Contacts as $C) {
                $Notification = new LkNotification($C->client_id, $C->id, 'add_pay_notif', $pay->sum_rub);
                $Notification->send();
            }
        }
    }

    public static function markAsSent($clientId, $fld)
    {
        global $db;
        $db->QueryUpdate("lk_client_settings", "client_id", array("client_id"=>$clientId, $fld."_sent" => array('NOW()'), "is_".$fld."_sent" => 1));
    }

    public static function unmarkAsSent($clientId, $fld)
    {
        global $db;
        $db->QueryUpdate("lk_client_settings", "client_id", array("client_id"=>$clientId, "is_".$fld."_sent" => 0));
    }
}

