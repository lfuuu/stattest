<?php

use app\models\Number;
use app\classes\StatModule;
use app\models\Trouble;
use app\models\LkWizardState;
use app\models\Business;
use app\models\BusinessProcessStatus;
use app\models\ClientAccount;
use app\models\TariffVirtpbx;
use app\models\TariffVoip;
use app\models\User;
use app\models\Organization;
use app\forms\comment\ClientContractCommentForm;
use app\forms\usage\UsageVoipEditForm;
use app\models\UsageVoip;
use app\models\DidGroup;

use app\forms\client\ClientCreateExternalForm;

if (isset($_GET) && isset($_GET["test"])) {
    define('YII_ENV', 'test');
}

define('NO_WEB',1);
define("PATH_TO_ROOT",'../../stat/');
header("Content-Type: text/html; charset=UTF-8");
include PATH_TO_ROOT."conf_yii.php";

$db->Connect();

class UserService
{
	function Get($field)
	{
		return 25;
	}
}
$user = new UserService();

$action=get_param_raw('action','');

if ($action=='add_client') {
    $V = array(
        'company' => 'company',
        'fio'     => 'fio', 
        'contact' => 'contact_phone',
        'email'   => 'email',
        'phone'   => 'official_phone',
        'fax'     => 'fax',
        'address' => 'address',
        'client_comment' => 'comment', 
        'vats_tariff_id' => 'vats_tariff_id'
    );

	$P = [];
    foreach ($V as $k1 => $k2) {
        $P[$k2] = get_param_raw($k1, "");
    }

    if ($P["company"] == "google") {
        echo "error:";
        exit();
    }

    $f = new ClientCreateExternalForm;
    $f->setAttributes($P);

    $result_message = "";
    if ($f->validate()) {
        $result_message = $f->create();
    } else {
        $errors = $f->getErrors();
        $fields = array_keys($errors);

        $result_message = "error:" . $errors[$fields[0]][0];
    }

    die($result_message);
}elseif($action == "set_active")
{

    if(get_param_raw("password", "") != "7f6a7f509ddd33f21b4f165aebdab4be")
    {
    	echo "error:wrong password";
    	exit();
    }

    $bill_no = get_param_raw("bill_no", "");

    if($b = $db->GetValue("select bill_no from newbills where bill_no = '".$db->escape($bill_no)."'"))
    {
        $db->Query("update newbills set editor = 'stat' where bill_no = '".$b."'");
        $t = $db->GetRow("select id, cur_stage_id from tt_troubles where bill_no = '".$b."'");
        if($t)
        {
        	$s = $db->GetRow("select * from tt_stages where stage_id = '".$t["cur_stage_id"]."'");

        	$dateStart = date("Y-m-d H:i:s");
        	$dateStart2 = date("Y-m-d H:i:s", strtotime("+1 hour"));

        	$R = array(
        		"trouble_id" => $t["id"],
        		"state_id" => $s["state_id"],
        		"user_main" => $s["user_main"],
        		"date_start" => $dateStart,
	        	"date_finish_desired" => $dateStart2
        	);

        	$s["date_edit"] = $s["date_finish_desired"] = array("NOW()");



        	$comment = get_param_raw("comment","");
        	$s["comment"] = ($comment ? $comment."<hr>" : "")."заявка передана в stat";

        	$db->QueryUpdate("tt_stages", "stage_id", $s);
        	$sId = $db->QueryInsert("tt_stages", $R);
        	$db->Query("update tt_troubles set cur_stage_id = '".$sId."' where id = '".$t["id"]."'");
        }
        echo "ok";
    }else{
        echo "error: bill not found";
    }

}elseif($action == "get_free_numbers")
{

    $region = isset($_GET["region"]) ? (int)$_GET["region"] : null;

    $numbers = Number::dao()->getFreeNumbersByRegion($region);

    foreach($numbers as $r) {
        echo implode(';', [$r->number, $r->beauty_level, $r->price, $r->region]) . "\n";
    }

}elseif($action == "reserve_number")
{
    $client_id = get_param_integer("client_id", 0);
    $numbers = get_param_protected("number","");
    $numbers = $_numbers = explode(',', $numbers);
    $numbers = "'".implode("','", $numbers)."'";

    $comment = "Reserve numbers: <br/>\n";
    $res = $db->AllRecords("select number,price from voip_numbers where number in (".$numbers.")");
    foreach($res as $r)
    {
        $comment .= $r['number'].' - '.$r['price']."<br/>\n";
    }

    $account = ClientAccount::findOne($client_id);
    if (!$account)
        return 0;


    $c = new ClientContractCommentForm;
    $c->contract_id = $account->contract_id;
    $c->user = 'auto';
    $c->comment = $comment;
    $c->save();

    $isOk = true;
    foreach($_numbers as $number)
    {
        try{
            $reservInfo = VoipReservNumber::reserv($number, $client_id, 1, null, true);

            if ($reservInfo)
            {
                $trouble = Trouble::findOne([
                    "client" => $account->client,
                    "trouble_type" => "connect",
                    "service" => ""
                    ]
                );

                if ($trouble)
                {
                    $trouble->service = "usage_voip";
                    $trouble->service_id = $reservInfo["usage_id"];
                    $trouble->save();
                }
            }

        } catch (Exception $e)
        {
            $isOk = false;
            if (YII_ENV == "test") {
                throw $e;
            } else {
                mail("adima123@yandex.ru", "voip reserv error", "Number: ".$number.", clientId: ".$client_id."\n".$e->GetMessage());
            }
        }
    }
    echo $isOk ? "1" : "0";

} elseif ($action == "connect_line")
{
    $clientId = get_param_raw("client_id", 0);
    $tarifId = get_param_raw("tarif_id", 0);

    try{
        $client = ClientAccount::findOne(["id" => $clientId]);

        if (!$client)
            throw new Exception("Клиент не найден");

        $tarif = TariffVoip::findOne(
            [
                "connection_point_id" => $client->region,
                "currency_id" => $client->currency,

                "id" => $tarifId
            ]
        );

        if (!$tarif)
            throw new Exception("Тариф не найден");

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $form = new UsageVoipEditForm;
            $form->scenario = 'add';
            $form->initModel($client);

            $form->city_id = $client->region;
            $form->tariff_main_id = $tarif->id;
            $form->type_id = "line";

            $form->prepareAdd();

            if (!$form->validate() || !$form->add()) 
            {
                if ($form->errors)
                {
                    \Yii::error($form);
                    $errorKeys = array_keys($form->errors);
                    throw new \Exception($form->errors[$errorKeys[0]][0], 500);
                } else {
                    throw new \Exception("Unknown error", 500);
                }
            }
            $transaction->commit();

            echo "ok:".$form->did;

        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    } catch(\Exception $e) {
        echo "error:".$e->GetMessage();
    }

}elseif($action == "stat_voip")
{
    if(!isset($_GET["d"])) die("error: empty params");

    $s = new m_stats();

    $d = $_GET["d"];
    if(!($d = @unserialize($d))) die("error: params is bad");

    list($region,$from,$to,$detality,$client_id,$usage_arr,$paidonly ,$skipped , $destination,$direction, $timezone) = $d;

    $dt = new DateTime();
    $dt->setTimeZone(new DateTimeZone('Europe/Moscow'));

    $a = $s->GetStatsVoIP($region,$from+$dt->getOffset(),$to+$dt->getOffset(),$detality,$client_id,$usage_arr,$paidonly ,$skipped , $destination,$direction, "Europe/Moscow");


    echo serialize($a);


}elseif($action == "stat_find")
{
    if(!isset($_GET["d"])) die("error: empty params");

    $s = new m_stats();

    $d = $_GET["d"];
    if(!($d = @unserialize($d))) die("error: params is bad");

    list($region,$from,$to,$find) = $d;
    
    $a = $s->FindByNumber($region,$from,$to,$find);

    echo serialize($a);
}

