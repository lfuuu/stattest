<?php
define("PATH_TO_ROOT", "../../");
include PATH_TO_ROOT."conf.php";

$contacts = LkNotificationContact::getList();

$clientUnset = array();

foreach ($contacts as $contact) {

    foreach (array(
                    "min_balance"  => array("eq" => "lt", "eq_field" => "balance"),
                    "day_limit"    => array("eq" => "gt", "eq_field" => "day_sum"),
                    "zero_balance" => array("eq" => "lt", "eq_field" => "balance"),
                    ) as $fld => $conf)
    {
        if (isset($contact->{$fld}) &&  $contact->{'is_'.$fld})
        {
            if (
                    ($contact->{$conf["eq_field"]} < $contact->{$fld} && $conf["eq"] == "lt") || 
                    ($contact->{$conf["eq_field"]} >= $contact->{$fld} && $conf["eq"] == "gt")
               )
            {
                if (!$contact->{'is_'.$fld.'_sent'})
                {
                    echo "\n(+) ".date("r").": client_id: ".$contact->client_id.", contact_id: ".$contact->id.", field: ".$conf["eq_field"].": ".$contact->{$conf["eq_field"]}.", value: ".$contact->{$fld};
                    $Notification = new LkNotification($contact->client_id, $contact->id, $fld, $contact->{$conf["eq_field"]}, $contact->balance);
                    if ($Notification->send()) {
                        LkNotificationContact::markAsSent($contact->client_id, $fld);
                        LkNotificationLog::contact_setEvent($contact, $fld, $contact->{$conf["eq_field"]});

                    }
                }
            }

            if (
                    ($contact->{$conf["eq_field"]} >= $contact->{$fld} && $conf["eq"] == "lt") || 
                    ($contact->{$conf["eq_field"]} < $contact->{$fld} && $conf["eq"] == "gt")
               )
            {
                if ($contact->{'is_'.$fld.'_sent'})
                {
                    echo "\n(-) ".date("r").": client_id: ".$contact->client_id.", field: ".$conf["eq_field"].": ".$contact->{$conf["eq_field"]}.", value: ".$contact->{$fld};
                    LkNotificationContact::unmarkAsSent($contact->client_id, $fld);

                    $clientUnset[$contact->client_id][$fld] = array("balance" => $contact->balance, "limit" => $contact->{$fld}, "value" => $contact->{$conf["eq_field"]});
                }
            }
        }
    }
}

if ($clientUnset) {
    foreach ($clientUnset as $clientId => $data)
    {
        foreach ($data as $fld => $fldData)
        {
            LkNotificationLog::contact_unSetEvent($clientId, $fld, $fldData);
        }
    }
}
