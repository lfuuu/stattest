<?
	define("PATH_TO_ROOT",'../stat/');
	include PATH_TO_ROOT."conf_yii.php";
	$code=get_param_raw('code','');
	$p=data_decode($code);
	$p = explode('-',$p);
	$p = array(isset($p[0])?intval($p[0]):0,isset($p[1])?intval($p[1]):0);
	if (!($r = $db->GetRow('select * from client_contracts where id="'.$p[0].'" and client_id='.$p[1].' limit 1'))) return;

	if ($img=get_param_raw('img')) {
		if ($_SERVER['HTTP_REFERER']!=PROTOCOL_STRING.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'].'?code='.urlencode($code)) return;
		header ('Content-type: image/gif');
		if ($img=='sign') {
			readfile('images/sign1.gif');
		} else {
			readfile('images/stamp.gif');
		}
	} else {
		$design->assign('code',$code);
		\app\classes\StatModule::clients()->clients_print($r['id'],'contract');
		$design->Process();
	}
?>
