<?php

define("NO_WEB", 1);
define('QUERY_PACKET_SIZE_LIMIT', (1024*1024)*15 ); //15Мб
define('PATH_TO_ROOT','../../');
include PATH_TO_ROOT."conf_yii.php";


echo "\n".date("r");

    try {

    $accs = $db->AllRecords("select c.id as client_id, t.description as tarif_name from clients c, usage_sms u
        left join tarifs_sms t on (t.id = u.tarif_id)
        where u.client = c.client and cast(now() as date) >= actual_from and cast(now() as date) <= actual_to and u.`status`= 'working'");

        $thiamis = mysql_connect("thiamis.mcn.ru", 'sms_stat', 'yeeg5oxGa', true);
        if(!$thiamis)
            throw new Exception(mysql_error());

        $res = mysql_select_db("sms2", $thiamis);
        if (!$res)
            throw new Exception(mysql_error());

        $res = mysql_query("set names utf8",$thiamis);
        if (!$res)
            throw new Exception(mysql_error());

        $res = mysql_query("start transaction",$thiamis);
        if (!$res)
            throw new Exception(mysql_error());

        $res = mysql_query("delete from sms_account",$thiamis);
        if (!$res)
            throw new Exception(mysql_error());

        $query = "insert ignore into `sms_account`(`client_id`,`tarif_name`) values (";
        $cnt = 0;
        foreach($accs as $a)
        {
            $query .= $a['client_id'].',"'.mysql_real_escape_string($a['tarif_name']).'"),(';
        }
        $query = substr($query,0,strlen($query)-2);

        echo "\n".$query;
        $res = mysql_query($query, $thiamis);
        if (!$res)
            throw new Exception(mysql_error());

        $res = mysql_query("commit",$thiamis);
        if (!$res)
            throw new Exception(mysql_error());
}catch(Exception $e)
{
    echo "\nError: ".$e->GetMessage();
    mail("adima123@yandex.ru", "sms set active account", $e->GetMessage());
}
?>
