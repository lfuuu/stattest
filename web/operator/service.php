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

if (isset($_GET) && isset($_GET["test"]))
{
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
$user	= new UserService();

$action=get_param_raw('action','');
if ($action=='add_client') {
	$V = array('company','fio', 'contact','email','phone','fax','address','market_chanel','client_comment', 'phone_connect');
	$P = array();
	foreach ($V as $k) @$P[$k] = htmlspecialchars(trim(get_param_raw($k, "")));

	if(empty($P["company"]))
    {
        $P["company"] = "Клиент с сайта";
    }

    if ($P["company"] == "google")
    {
        echo "error:";
        exit();
    }
    $c = \app\models\ClientContact::findOne(['data' => $P['email'], 'type' => 'email']);

    Yii::info("Start add_client");
    Yii::info($P);
    Yii::info($c);

    $clientId = null;
	if($c)
    {
        $clientId = $c->client_id;
    } else {

        $s = new \app\models\ClientSuper();
        $s->name = $P['company'];
        $s->validate();
        $s->save();

        Yii::info($s);

        $cg = new \app\forms\client\ContragentEditForm(['super_id' => $s->id]);
        $cg->name = $cg->name_full = $P['company'];
        $cg->address_jur = $P['address'];
        $cg->legal_type = 'legal';
        $cg->validate();
        $cg->save();

        Yii::info($cg);

        $cr = new \app\forms\client\ContractEditForm(['contragent_id' => $cg->id]);
        $cr->business_id = Business::TELEKOM;
        $cr->business_process_id = \app\models\BusinessProcess::TELECOM_SUPPORT;
        $cr->business_process_status_id = BusinessProcessStatus::TELEKOM_MAINTENANCE_ORDER_OF_SERVICES;
        $cr->organization_id = Organization::MCN_TELEKOM;
        $cr->validate();

        $cr->save();

        Yii::info($cr);

        $ca = new \app\forms\client\AccountEditForm(['id' => $cr->newClient->id]);
        $ca->address_post = $P['address'];
        $ca->address_post_real = $P['address'];
        $ca->address_connect = $P['address'];
        $ca->sale_channel = $P['market_chanel'];
        $ca->status = "income";

        if($P["phone_connect"])
            $ca->phone_connect = $P["phone_connect"];

        $ca->validate();

        if ($ca->save()) {
            $clientId = $ca->id;

            Yii::info($ca);
            $contactId = 0;
            if($P['contact']){
                $c = new \app\models\ClientContact();
                $c->client_id = $ca->id;
                $c->type = 'phone';
                $c->data = $P['contact'];
                $c->comment = $P["fio"];
                $c->user_id = User::CLIENT_USER_ID;
                $c->is_active = 1;
                $c->is_official = 0;
                $c->save();
            }
            if($P['phone']){
                $c = new \app\models\ClientContact();
                $c->client_id = $ca->id;
                $c->type = 'phone';
                $c->data = $P['phone'];
                $c->comment = $P["fio"];
                $c->user_id = User::CLIENT_USER_ID;
                $c->is_active = 1;
                $c->is_official = 1;
                $c->save();
            }
            if($P['fax']){
                $c = new \app\models\ClientContact();
                $c->client_id = $ca->id;
                $c->type = 'fax';
                $c->data = $P['fax'];
                $c->comment = $P["fio"];
                $c->user_id = User::CLIENT_USER_ID;
                $c->is_active = 1;
                $c->is_official = 1;
                $c->save();
            }
            if($P['email']){
                $c = new \app\models\ClientContact();
                $c->client_id = $ca->id;
                $c->type = 'email';
                $c->data = $P['email'];
                $c->comment = $P["fio"];
                $c->user_id = User::CLIENT_USER_ID;
                $c->is_active = 1;
                $c->is_official = 1;
                $c->save();
                $contactId = $c->id;
            }

            $R = array(
                'trouble_type' => 'connect',
                'trouble_subtype' => 'connect',
                'client' => "id".$ca->id,
                'date_start' => date('Y-m-d H:i:s'),
                'date_finish_desired' => date('Y-m-d H:i:s'),
                'problem' => "Входящие клиент с сайта: ".$P["company"],
                'user_author' => "system",
                'first_comment' => $P["client_comment"]
            );

            $troubleId = StatModule::tt()->createTrouble($R, "system");
            LkWizardState::create($cr->id, $troubleId);
        }
    }

    if ($clientId)
    {
        if ($vatsTarifId = get_param_integer("vats_tariff_id", 0)) // заявка с ВАТС
        {
            $client = ClientAccount::findOne(['id' => $clientId]);
            $tarif = TariffVirtpbx::findOne([['id' => $vatsTarifId], ['!=', 'status', 'archive']]);

            if ($client && $tarif)
            {
                $actual_from = date('Y-m-d');
                $actual_to = '4000-01-01';
                $vats = new UsageVirtpbx;
                $vats->client = $client->client;
                $vats->activation_dt = (new DateTime($actual_from, new DateTimeZone($client->timezone_name)))->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
                $vats->expire_dt = (new DateTime($actual_to, new DateTimeZone($client->timezone_name)))->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
                $vats->actual_from = $actual_from;
                $vats->actual_to = $actual_to;
                $vats->amount = 1;
                $vats->status = 'connecting';
                $vats->region = \app\models\Region::MOSCOW;
                $vats->save();
                $logTarif = new LogTarif;
                $logTarif->service = 'usage_virtpbx';
                $logTarif->id_service = $vats->id;
                $logTarif->id_tarif = $tarif->id;
                $logTarif->ts = (new DateTime())->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
                $logTarif->date_activation = date('Y-m-d');
                $logTarif->id_user = User::LK_USER_ID;
                $logTarif->save();

                if ($tarif->id == TariffVirtpbx::TEST_TARIFF_ID) {
                    $usage = UsageVoip::findOne(['client' => $client->client]);

                    if (!($usage instanceof UsageVoip)) {
                        $freeNumber = Number::dao()->getRandomFreeNumber(DidGroup::MOSCOW_STANDART_GROUP_ID);

                        if (!($freeNumber instanceof Number)) {
                            throw new Exception('Not found free number into 499 DID group', 500);
                        }

                        $transaction = Yii::$app->db->beginTransaction();
                        try {
                            $form = new UsageVoipEditForm;
                            $form->scenario = 'add';
                            $form->initModel($client);
                            $form->did = $freeNumber->number;
                            $form->prepareAdd();
                            $form->tariff_main_id = VoipReservNumber::getDefaultTarifId($client->region, $client->currency);
                            $form->create_params = \yii\helpers\Json::encode([
                                'type' => $form->type_id,
                                'sip_accounts' => 0,
                                'vpbx_stat_product_id' => $vats->id,
                            ]);

                            if (!$form->validate() || !$form->add()) {
                                if ($form->errors) {
                                    \Yii::error($form);
                                    $errorKeys = array_keys($form->errors);
                                    throw new Exception($form->errors[$errorKeys[0]][0], 500);
                                } else {
                                    throw new Exception('Unknown error', 500);
                                }
                            }

                            $usageVoipId = $form->id;

                            $transaction->commit();
                        }
                        catch (\Exception $e) {
                            $transaction->rollBack();
                            throw $e;
                        }
                    }
                }
            }
        }
    }

    if ($clientId)
    {
        die("ok:".$clientId);
    } else {
        die("error:");
    }
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

    $numbers =
        Yii::$app->db->createCommand("
                SELECT * FROM `voip_numbers` WHERE (`region`=:region) AND (`status`='instock')
                HAVING 
                    if(
                        number like '7495%', 
                        number like '74951059%' or number like '74951090%' or beauty_level in (1,2), 
                        true
                    )
                ORDER BY if(beauty_level=0, 10, beauty_level) DESC, number  ",
            [':region' => $region]
        )
            ->queryAll();

    foreach($numbers as $r) {
        echo $r['number'].';'.$r['beauty_level'].';'.$r['price'].';'.$r['region']."\n";
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

