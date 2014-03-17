<?php

define('NO_WEB',1);
define('PATH_TO_ROOT','./');
include PATH_TO_ROOT."conf.php";

include INCLUDE_PATH."class.phpmailer.php";
include INCLUDE_PATH."class.smtp.php";

$db->Query("set names utf8");


$clients = array('id4102',
'id18620',
'id18698',
'id20000',
'id20135',
'id21415',
'id21832',
'id23354',
'id23385',
'taxi/2');

$clients = array('id20000');


$R = array();
foreach($db->AllRecords("select id, client, credit from clients where credit > -1 and status='work' /*and client in ('".implode("', '", $clients)."')*/") as $l)
{

    echo "\n".$l["client"].": (".$l["credit"].")";


    /*

    $usages = array();
    foreach($db->AllRecords("select id from usage_voip where client = '".$l["client"]."'") as $u)
        $usages[] = $u["id"];

    if (!$usages) 
    {
        echo " no usages";
        continue;
    }
    */

    /*
    $r = $pg_db->GetRow("SELECT cast(sum(amount)/100.0 as numeric(10,2))as sum, min(time) as min, max(time) as max FROM calls.calls WHERE time > '2013-09-01 00:00:00' AND usage_id IN (".implode(", ", $usages).") LIMIT 1000 OFFSET 0");

    $r["min"] = strtotime($r["min"]);
    $r["max"] = strtotime($r["max"]);

    $days = round(($r["max"] - $r["min"])/86400);

    if (!$days) 
    {
        echo "no calls";
        continue;
    }

    $perDay = round($r["sum"] / $days, 2);

    $limit = $perDay * 30.41 *0.10;

    $realLimit = round($limit, -2);

    $realLimit = $realLimit < 100 ? 100 : $realLimit;

    if($realLimit < 100) continue;

    */

    $balance = Api::getBalance($l["id"]);

    $lastAbon = getLastMonthSumAbon($l["id"]);

    if (!$lastAbon || $lastAbon <= 0) continue;

    /*
    $a = array( 
        "days"      => $days,
        "perday"    => $perDay,
        "limit"     => $limit, 
        "reallimit" => $realLimit,
        "balance"   => $balance,
        "days2"     => round($balance/$perDay),
        "---"       => ($balance < $realLimit ? "!!!" : ""),
        "abon"      => $lastAbon,
        "str"       => "На ваше"
        );
    $a["real_balance"] = $balance-$a["abon"];
    */


    $a = array( 
        "client" => $l["client"],
        "balance"   => $balance,
        "abon"      => $lastAbon,
        );

    $R[$l["id"]] = $a;

    print_r($a);
}

if (!$R) exit();

$Mail = new PHPMailer();
$Mail->SetLanguage("ru","include/");
$Mail->CharSet = "utf-8";
$Mail->From = "info@mcn.ru";
$Mail->FromName="МСН Телеком";
$Mail->Mailer='smtp';
$Mail->Host=SMTP_SERVER;

$Mail->ContentType='text/html';
$Mail->Subject = "Уведомление об остатке средств на лицевом счете МСН Телеком";
$Mail->IsHTML(true);

$template = file_get_contents("./mail.before_billing");

foreach ($R as $clientId => $a)
{
    $emails = getContactsForSend($clientId);
    /*
    $ee = "";
        foreach ($emails as $email)
            $ee .= ", ".$email;
            */

    //$emails = array("adima123@yandex.ru");
    if (!$emails) continue;

    foreach ($emails as $email)
    {
        $Mail->AddAddress($email);
    }

    $Mail->Body = /*$ee.*/template($template, $a);
    $Mail->Send();

    $Mail->ClearAddresses();
    $Mail->ClearAttachments();
}

function template($t, $a)
{
    foreach($a as $k => $v)
        $t = str_replace("{\$".$k."}", $v, $t);

    return $t;
}


function getLastMonthSumAbon($clientId)
{
    global $db;


    $prevMonth = strtotime("first day of previous month, midnight");

    $r = $db->GetValue($sql = "SELECT sum(l.sum) FROM `newbills` b, newbill_lines l where b.bill_no = l.bill_no and client_id = '".$clientId."' and b.bill_no like '".date("Ym", $prevMonth)."-%' and item like 'Абонен%'");

    return $r;
}

function getContactsForSend($clientId)
{
    global $db;

    $a = array();

    foreach ($db->AllRecords("SELECT data FROM `client_contacts` WHERE `client_id` = '".$clientId."' AND `type` = 'email' AND `is_active` = '1' AND `is_official` = '1' LIMIT 0, 1000") as $l)
    {
        $a[] = $l["data"];
    }

    return $a;
}

