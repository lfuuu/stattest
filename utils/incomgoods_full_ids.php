<?php

define('NO_WEB',1);
define('PATH_TO_ROOT','../');
include PATH_TO_ROOT."conf.php";



foreach($db->AllRecords("
            SELECT t.id, client, bill_no, max(date) max_date FROM `tt_troubles`  t
            left join g_income_order o ON (o.number = t.bill_no)
            where bill_no like '__-%'
#order by bill_no, date
            group by t.bill_no
#having c > 1

#       limit 1
            ") as $l)
{
    $o = GoodsIncomeOrder::find(array("conditions" => array("date" => $l["max_date"], "number" => $l["bill_no"])));

    print_r($l);
    print_r($o->id);

    $db->QueryUpdate("tt_troubles", "id", array("id" => $l["id"], "bill_id" => $o->id));
}


