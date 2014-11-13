<?php

define('NO_WEB',1);
define('PATH_TO_ROOT','../../');
include PATH_TO_ROOT."conf_yii.php";

include INCLUDE_PATH."class.phpmailer.php";
include INCLUDE_PATH."class.smtp.php";

$db->Query("set names utf8");

$prevMonthStart = strtotime("first day of previous month, 00:00:00");


echo "\n".date("r").":\n";


$R = array();
foreach($db->AllRecords("SELECT id, client, credit FROM clients WHERE credit > -1 AND status='work'") as $l)
{
    echo "\n";
    echoCell($l['client'], 15);
    echoCell("credit: ".$l["credit"]."");

    $usages = array();
    foreach($db->AllRecords(
        "SELECT 
            id
        FROM 
            usage_voip 
        WHERE 
                client = '".$l["client"]."' 
            AND actual_to > '".date("Y-m-d", $prevMonthStart)."' 
            AND actual_from < '2029-01-01'") as $u)
    {
        $usages[] = $u["id"];
    }


    $isSayFirstCell = false;
    $monthCallSum = $monthCallSumRound = $callSumPerDay = 0 ;

    if (!$usages) 
    {
        echoCell("no usages", 19);
        $isSayFirstCell = true;
    } else {
        $r = $pg_db->GetRow(
                "SELECT 
                    cast(sum(amount)/100.0 as numeric(10,2)) as sum, 
                    min(time) as min, 
                    max(time) as max 
                FROM 
                    calls.calls 
                WHERE 
                        time > '".date("Y-m-d", $prevMonthStart)." 00:00:00' 
                    AND usage_id IN (".implode(", ", $usages).")
                ");


        $r["min"] = strtotime($r["min"]);
        $r["max"] = strtotime($r["max"]);

        $days = round(($r["max"] - $r["min"])/86400);

        if (!$days)
        {
            echoCell("no calls", 19);
            $isSayFirstCell = true;
            $monthCallSum = $monthCallSumReal = $callSumPerDay = 0 ;
        } else {
            $callSumPerDay = $r["sum"] / $days;
            $monthCallSum = round(date("t")*$callSumPerDay*1.1); //прогноз, +10%
            $monthCallSumRound = round($monthCallSum < 100 ? 100 : $monthCallSum,  -2);
        }
    }


    if ($monthCallSum)
    {
        echoCell("perDay: ".round($callSumPerDay, 2), 19);
        echoCell("month Call Sum: ".$monthCallSum, 25);
        echoCell("rounded: ".$monthCallSumRound, 20);
    } else {
        if( !$isSayFirstCell)
            echoCell("", 19);

        echoCell("", 45);
    }

    $balance = Api::getBalance($l["id"]);
    $abon = Bill::getPreBillAmount($l["id"]);
    $forecastBalance = $balance - $monthCallSumRound - $abon;

    $isNeedSend = -$l["credit"] > $forecastBalance;

    echoCell("balance: ".$balance, 23);
    echoCell("abon: ".$abon, 17);
    echoCell("forecastBalance: ".$forecastBalance, 28);
    echoCell("is need send: ".(-$isNeedSend ? "yes": "no"));

    if ($isNeedSend)
    {
        if ($l["credit"] > 0)
        {
            $credit_text = 'Допустимый кредитный лимит ' . $l["credit"] . ' рублей';
        } else {
            $credit_text = 'Обращаем Ваше внимание, что при достижении нулевого баланса лицевого счета услуги будут автоматически заблокированы';
        }
        $R[$l["id"]] = array(
            "client" => $l["client"],
            "balance" => $balance,
            "abon" => $abon,
            "credit" => $credit_text
            );
    }
}

if (!$R) 
    exit();

$Mail = new PHPMailer();
$Mail->SetLanguage("ru",PATH_TO_ROOT."include/");
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

    if (!$emails) {
        echo "\t no contacts";
        continue;
    }

    foreach ($emails as $contactId => $email)
    {
        $Mail->AddAddress($email);
    }

    $Mail->Body = template($template, $a);

    if($Mail->Send()) {
        LkNotificationLog::addLogRaw($clientId, 0, "prebil_prepayers_notif", true, $a["balance"], 0, $a["abon"]);
    }

    $Mail->ClearAddresses();
    $Mail->ClearAttachments();

}

function template($t, $a)
{
    foreach($a as $k => $v)
        $t = str_replace("{\$".$k."}", $v, $t);

    return $t;
}


function getContactsForSend($clientId)
{
    global $db;

    $a = array();

    foreach ($db->AllRecords("SELECT id, data FROM `client_contacts` WHERE `client_id` = '".$clientId."' AND `type` = 'email' AND `is_active` = '1' AND `is_official` = '1' LIMIT 0, 1000") as $l)
    {
        $a[$l["id"]] = $l["data"];
    }

    return $a;
}

function echoCell($str, $len=15)
{
    $str = " ".$str;
    echo $str.(strlen($str) < $len ? str_pad(" ", $len-strlen($str)) : "");
}

