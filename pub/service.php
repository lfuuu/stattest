<?php
define('NO_WEB',1);
define("PATH_TO_ROOT",'../');
header("Content-Type: text/html; charset=UTF-8");
include PATH_TO_ROOT."conf.php";
class User
{
	function Get($field)
	{
		return 25;
	}
}
//require_once(INCLUDE_PATH.'user.php');
$user	= new User();
//$user->DoAction("");
//if (!$user->IsAuthorized()) die('error:authorization failed');

$action=get_param_raw('action','');
if ($action=='add_client') {
	$V = array('company','fio', 'contact','email','phone','fax','address','market_chanel','client_comment', 'phone_connect');
	$P = array();
	foreach ($V as $k) @$P[$k] = iconv('UTF-8','KOI8-R',trim(get_param_raw($k)));

	if(empty($P["company"]))
	{
		die("error: ".iconv('KOI8-R','UTF-8',"��� �������� �� ������!"));
	}

	if($id = $db->GetValue("select id from clients where company = '".mysql_escape_string($P["company"])."'"))
	{
		die("ok:".$id);
	}
	$O = new ClientCS();
	$O->client = "idNNNN";
	$O->company = $P['company'];
	$O->company_full = $P['company'];
	$O->address_post = $P['address'];
	$O->address_post_real = $P['address'];
	$O->address_connect = $P['address'];
	$O->address_jur = $P['address'];
	$O->sale_channel = $P['market_chanel'];
	if($P["phone_connect"])
		$O->phone_connect = $P["phone_connect"];

	$O->firma="mcn_telekom";
	$O->status = 'income';
	if ($O->Create(0)) {
		if($P['contact']) $O->AddContact('phone',$P['contact'],$P["fio"],0);
		if($P['phone']) $O->AddContact('phone',$P['phone'],$P["fio"],1);
		if($P['fax'])  $O->AddContact('fax',$P['fax'],$P["fio"],1);
		if($P['email']) $O->AddContact('email',$P['email'],$P["fio"],1);
		$O->Add('income',$P['client_comment']);
		echo 'ok:'.$O->id;
	} else {
		echo 'error:';
	}
}elseif($action == "set_active")
{

    if(get_param_raw("password", "") != "7f6a7f509ddd33f21b4f165aebdab4be")
    {
    	echo "error:wrong password";
    	exit();
    }

    $bill_no = get_param_raw("bill_no", "");

    if($b = $db->GetValue("select bill_no from newbills where bill_no = '".mysql_escape_string($bill_no)."'"))
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
        	$s["comment"] = ($comment ? $comment."<hr>" : "")."������ �������� � stat";

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

	$res = $db->AllRecords("
          select a.*, (select max(actual_to) from usage_voip where e164 = a.number and not (actual_from = '2029-01-01' and actual_to='2029-01-01')) date_to
          from (

            select number,beauty_level,price,region
	                          from voip_numbers
	                          where client_id is null and
                                (
                                  (used_until_date is null or used_until_date < now() - interval 6 MONTH)
                                  or
                                  (number like '7495%' and (used_until_date is null or used_until_date < now()))
                                 ) ".($region !== null ? " and region = '".$region."'" : "")."

              )a
          having date_to is null or date_to < now()
          order by if(beauty_level=0, 10, beauty_level) desc, number
	                        ");
	foreach($res as $r)
	{
		echo $r['number'].';'.$r['beauty_level'].';'.$r['price'].';'.$r['region']."\n";
	}
}elseif($action == "reserve_number")
{
    $client_id = get_param_integer("client_id", 0);
	$numbers = mysql_escape_string( get_param_raw("number","") );
	$numbers = explode(',', $numbers);
	$numbers = "'".implode("','", $numbers)."'";

  $comment = "Reserve numbers: <br/>\n";
  $res = $db->AllRecords("select number,price from voip_numbers where number in (".$numbers.")");
  foreach($res as $r)
  {
    $comment .= $r['number'].' - '.$r['price']."<br/>\n";
  }
  $db->QueryInsert('client_statuses', array('id_client'=>$client_id,'comment'=>$comment,'user'=>'auto'));

	$db->Query("update voip_numbers set client_id=".$client_id.", edit_user_id=0 where client_id is null and number in (".$numbers.")");
	echo $db->AffectedRows();
}elseif($action == "stat_voip")
{
    if(!isset($_GET["d"])) die("error: empty params");

    include "../modules/stats/module.php";

    $s = new m_stats();

    $d = $_GET["d"];
    if(!($d = @unserialize($d))) die("error: params is bad");

    list($region,$from,$to,$detality,$client_id,$usage_arr,$paidonly ,$skipped , $destination,$direction) = $d;
    
    $a = $s->GetStatsVoIP($region,$from,$to,$detality,$client_id,$usage_arr,$paidonly ,$skipped , $destination,$direction);

    foreach($a as &$vv)
    {
        foreach($vv as &$v)
        {
            $v = iconv("koi8-r", "utf-8", $v);
        }
    }

    echo serialize($a);


}elseif($action == "stat_find")
{
    if(!isset($_GET["d"])) die("error: empty params");

    include "../modules/stats/module.php";

    $s = new m_stats();

    $d = $_GET["d"];
    if(!($d = @unserialize($d))) die("error: params is bad");

    list($region,$from,$to,$find) = $d;
    
    $a = $s->FindByNumber($region,$from,$to,$find);

    foreach($a as &$vv)
    {
        foreach($vv as &$v)
        {
            $v = iconv("koi8-r", "utf-8", $v);
        }
    }

    echo serialize($a);
}

