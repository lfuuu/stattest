<?php 

class LkNotificationContact
{
    private static $billingCounters = array();

    public static function getList()
    {
        $list = array();

        self::fillMinBalance($list);
        self::fillDayLimit($list);
        self::fillZeroBalance($list);

        return $list;
    }

    private static function fillMinBalance(&$list)
    {
        $res = ClientContact::find_by_sql("
            SELECT
                c.id as client_id,
                cc.id,
                cc.type,
                cc.data,
                ns.min_balance AS is_min_balance,
                cs.is_min_balance_sent,
                cs.min_balance,
                c.balance,
                c.credit
            FROM
                client_contacts cc
            LEFT JOIN lk_notice_settings ns ON cc.id=ns.client_contact_id
            LEFT JOIN lk_client_settings cs ON cs.client_id = ns.client_id
            LEFT JOIN clients c ON c.id=cc.client_id
            WHERE
                ns.status='working' AND
                c.status in ('work','connecting','testing', 'debt') AND
                (ns.min_balance='1')
            ");

        foreach ($res as &$r) 
        {
            if (!isset(self::$billingCounters[$r->client_id])) {
                self::$billingCounters[$r->client_id] = ClientCS::getBillingCounters($r->client_id);
            }

            if ($r->credit > -1) {
                $r->balance -= self::$billingCounters[$r->client_id]['amount_sum'];
            }

            $list[] = $r;
        }
    }

    private static function fillDayLimit(&$list)
    {
        $res = ClientContact::find_by_sql("
            SELECT
                cs.client_id,
                cc.id,
                cc.type,
                cc.data,
                ns.day_limit AS is_day_limit,
                cs.is_day_limit_sent,
                cs.day_limit,
                0 AS day_sum,
                c.balance,
                c.credit
            FROM
                client_contacts cc
            LEFT JOIN lk_notice_settings ns ON cc.id=ns.client_contact_id
            LEFT JOIN lk_client_settings cs ON cs.client_id = ns.client_id
            LEFT JOIN clients c ON c.id=cc.client_id
            WHERE
                ns.status='working' AND
                c.status in ('work','connecting','testing', 'debt') AND
                (ns.day_limit='1')
            ");

        foreach ($res as $r) 
        {
            if (!isset(self::$billingCounters[$r->client_id]))
                self::$billingCounters[$r->client_id] = ClientCS::getBillingCounters($r->client_id);

            $r->day_sum = self::$billingCounters[$r->client_id]['amount_day_sum'];

            if ($r->credit > -1) {
                $r->balance -= self::$billingCounters[$r->client_id]['amount_sum'];
            }

            $list[] = $r;
        }
    }

    private static function fillZeroBalance(&$list)
    {
        $res = ClientContact::find_by_sql("
            SELECT
                c.id as client_id,
                cc.id,
                cc.type,
                cc.data,
                1 as is_zero_balance,
                ifnull(cs.is_zero_balance_sent, 0) as is_zero_balance_sent,
                0 as zero_balance,
                c.balance,
                c.credit
            FROM
                client_contacts cc
            LEFT JOIN clients c ON c.id=cc.client_id
            LEFT JOIN lk_client_settings cs ON cs.client_id = c.id
            WHERE
                    c.status in ('work','connecting','testing', 'debt') 
                and cc.is_active and cc.is_official 
                and cc.type in ('email', 'sms')
                and c.credit > -1
            ");

        foreach ($res as &$r) 
        {
            if (!isset(self::$billingCounters[$r->client_id])) {
                self::$billingCounters[$r->client_id] = ClientCS::getBillingCounters($r->client_id);
            }

            if ($r->credit > -1) {
                $r->balance -= self::$billingCounters[$r->client_id]['amount_sum'];
            }

            $list[] = $r;
        }
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

                $client = ClientCard::find($C->client_id);

                if ($client->credit > -1) {
                    if (!isset(self::$billingCounters[$C->client_id]))
                        self::$billingCounters[$C->client_id] = ClientCS::getBillingCounters($C->client_id);

                    $client->balance -= self::$billingCounters[$C->client_id]['amount_sum'];
                }

                LkNotificationLog::addLogRaw($C->client_id, $C->id, 'add_pay_notif', true, $client->balance, 0, $pay->sum_rub);
            }
        }
    }

    public static function markAsSent($clientId, $fld)
    {
        global $db;

        $data = array(
            "client_id"  => $clientId, 
            $fld."_sent" => array('NOW()'), 
            "is_".$fld."_sent" => 1
        );

        if ( $db->GetValue("select client_id from lk_client_settings where client_id = '".$clientId."'") ) //if exists
        {
            $db->QueryUpdate("lk_client_settings", "client_id", $data);
        } else {

            $data["min_balance"] = 1000; // default values
            $data["day_limit"] = 200;

            $db->QueryInsert("lk_client_settings", $data);
        }
    }

    public static function unmarkAsSent($clientId, $fld)
    {
        global $db;
        $db->QueryUpdate("lk_client_settings", "client_id", array("client_id"=>$clientId, "is_".$fld."_sent" => 0));
    }
}

