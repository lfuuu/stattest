<?
class m_employeers {	
	var $actions=array(
					'default'		=> array('employeers','r'),
					'couriers'		=> array('employeers','r'),
				);

	//содержимое левого меню. array(название; действие (для проверки прав доступа); доп. параметры - строкой, начинающейся с & (при необходимости); картиночка ; доп. текст)
	var $menu=array(
					array('Сотрудники',		'default',	''),
					array('Курьеры',		'couriers',	''),
				);

	function m_employeers(){	
		
	
	}
	function GetPanel($fixclient){
		$R=array();
		foreach($this->menu as $val){
			$act=$this->actions[$val[1]];
			if (access($act[0],$act[1])) $R[]=array($val[0],'module=employeers&action='.$val[1].(isset($val[2])?$val[2]:''), (isset($val[3])?$val[3]:''),(isset($val[4])?$val[4]:''));
		}
		if (count($R)>0){
            return array('Сотрудники',$R);
		}
	}

	function GetMain($action,$fixclient){
		global $design,$db,$user;
		if (!isset($this->actions[$action])) return;
		$act=$this->actions[$action];
		if (!access($act[0],$act[1])) return;
		
		call_user_func(array($this,'employeers_'.$action),$action);
	}
	
	function employeers_default($action) {
		global $design,$db,$user;
		$cgroup=get_param_protected('group');
		$cuser=get_param_protected('user');
		if ($cuser) {
			$db->Query('select * from user_users where user="'.$cuser.'"');
			if (!($r=$db->NextRecord())) trigger_error2("Такого пользователя не существует");
			$cgroup=$r['usergroup'];	
			$design->assign('emp_user',$r);
		}
		if ($cgroup){
			$db->Query('select * from user_groups where usergroup="'.$cgroup.'"');
			if (!($r=$db->NextRecord())) trigger_error2("Такой группы не существует");
			$design->assign('emp_group',$r);
			
 			$db->Query('select * from user_users where usergroup="'.$cgroup.'"');
			$R=array(); while ($r=$db->NextRecord()) $R[]=$r;
			$design->assign('emp_users',$R);
		}
		$db->Query('select * from user_groups');
		$R=array(); while ($r=$db->NextRecord()) $R[]=$r;
		$design->assign('emp_groups',$R);

		$design->AddMain('employeers/main.tpl');
	}

    function employeers_couriers($action)
    {
        global $db, $design;


        // del
        $delId = get_param_protected("del");
        if ($delId) {
            $db->Query("select id, is_used from courier where id = '".$delId."'");
            $r=$db->NextRecord();
            if ($r) {
                if(!$r["is_used"]) {
                    $db->Query("delete from courier where id = '".$delId."'");
                }else{
                    $db->Query("update courier set enabled='no' where id = '".$delId."'");
                }
            }
        }


        $id = get_param_protected("id");
        $cId = "0";
        $cName = "";
        $cPhone = "";
        $cAll4geo = "";

        // edit
        if ($id)
        {
            $db->Query("select id, name, phone,all4geo from courier where id = '".$id."' and enabled='yes'");
            $r=$db->NextRecord();
            if ($r)
            {
                $cId = $r["id"];
                $cName = $r["name"];
                $cPhone = $r["phone"];
                $cAll4geo = $r["all4geo"];
            }
        }

        // save
        $getId = get_param_protected("cId", false);
        if ($getId !== false)
        {
            $getName = get_param_protected("cName");
            $getPhone = get_param_protected("cPhone");
            $getAll4geo = get_param_protected("cAll4geo");
            $getName = trim($getName);
            $getPhone = trim($getPhone);
            $getAll4geo = trim($getAll4geo);
            $error = false;
            if (empty($getName))
            {
                $error = true;
                trigger_error2("Имя не должно быть пустым");
            }else{
                $db->Query("select id from courier where name = '".$getName."' and id != '".$getId."'");
                $r=$db->NextRecord();
                if ($r){
                    $error = true;
                    trigger_error2("Такое имя уже есть");
                }
            }

            if(!empty($getAll4geo))
            {
                if($r = $db->GetValue("select name from courier where all4geo ='".$getAll4geo."' and id != '".$getId."' and enabled='yes'"))
                {
                    $error = true;
                    trigger_error2("Введенный All4Geo Ид используется у: ".$r);
                }
            }

            if($getPhone && !preg_match("/^79[0-9]{9}$/", $getPhone)){
                $error = true;
                trigger_error2("Не верный формат телефонного номера (79ххххххххх)");
            }

            if ($error)
            {
                $cId = $getId;
                $cName = $getName;
                $cPhone = $getPhone;
                $cAll4geo = $getAll4geo;
            }else{
                $sql = "set name = '".mysql_real_escape_string($getName)."', phone = '".$getPhone."', all4geo = '".$getAll4geo."'";
                if ($getId) {
                    $db->Query("update courier ".$sql." where id = '".$getId."'");
                }else{
                    $db->Query("insert into courier ".$sql);
                }
            }
        }
        $design->assign("cId", $cId);
        $design->assign("cName", $cName);
        $design->assign("cPhone", $cPhone);
        $design->assign("cAll4geo", $cAll4geo);

        // listing
        $db->Query("select * from courier where enabled='yes' order by name");
		$R=array(); while ($r=$db->NextRecord()) $R[]=$r;
		$design->assign('l_couriers',$R);

        $design->AddMain("employeers/couriers.tpl");


    }
}
	
?>
