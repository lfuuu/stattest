<?php
class m_users {
	var $rights=array(
					'users'		=>array('Работа с пользователями','r,change,grant','чтение,изменение,раздача прав')
				);
	var $actions=array(
					'default'		=> array('users','r'),
					'edit'			=> array('users','change'),
					'add'			=> array('users','change'),
					'delete'		=> array('users','change'),
				);

	//содержимое левого меню. array(название; действие (для проверки прав доступа); доп. параметры - строкой, начинающейся с & (при необходимости); картиночка ; доп. текст)
	var $menu=array(
					array('Операторы',	'default',	'&m=users'),
					array('Группы',		'default',	'&m=groups'),
					array('Отделы',		'default',	'&m=departs'),
				);

	function m_users(){


	}
	function Install($p){
		return $this->rights;
	}

	function GetPanel($fixclient){
		global $design,$user;
		$R=array();
		foreach($this->menu as $val){
			$act=$this->actions[$val[1]];
			if (access($act[0],$act[1])) $R[]=array($val[0],'module=users&action='.$val[1].(isset($val[2])?$val[2]:''), (isset($val[3])?$val[3]:''),(isset($val[4])?$val[4]:''));
		}
		if (count($R)>0){
			$design->AddMenu('Управление доступом',$R);
		}
	}

	function GetMain($action,$fixclient){
		global $design,$db,$user;
		if (!isset($this->actions[$action])) return;
		$act=$this->actions[$action];
		if (!access($act[0],$act[1])) return;

		$m=get_param_raw('m','users');
		if (!in_array($m,array('users','user','groups','group','depart','departs'))) return;
		$design->assign('CUR','?module=users&m='.$m);

		//внимание! - не .$action, а .$m, для того, чтобы не копировать общий код
		//проверка на доступ к такому action у нас уже вставлена выше
		//особенность - для разных m одинаковые action должны использовать одинаковые rights
		call_user_func(array($this,'users_'.$m),$action);
//		call_user_func(array($this,'tt_'.$action),$fixclient) - так выглядит обычная строка вызова, взятая из модуля tt
	}

	//просмотр списка пользователей
	function users_users($action) {
		global $design,$db,$user;
		$this->d_users_get($d_users);
		$design->assign_by_ref("users",$d_users);
		$this->d_groups_get($d_groups);
		$id=get_param_protected('id');

		if ($action=='delete'){
			if (!isset($d_users[$id])) {
				trigger_error('Такого оператора не существует');
			} else {
				$db->Query('delete from user_users where user="'.$id.'"');
				trigger_error('Оператор '.$id.' удалён');
				$this->d_users_get($d_users);
				$design->assign_by_ref("users",$d_users);
			}
		}
		$design->AddMain('users/main_users.tpl');
	}

	//работа со одним пользователем
	function users_user($action) {
		global $design,$db,$user;
		$this->d_users_get($d_users);
		$design->assign_by_ref("users",$d_users);
		$this->d_groups_get($d_groups);
		$this->d_departs_get($d_depart);
		$id=get_param_protected('id');
		$Firms = array(
		                'mcn_telekom'=>'ООО &laquo;МСН Телеком&raquo;',
		                'mcn'=>'ООО &laquo;Эм Си Эн&raquo;',
		                'markomnet_new'=>'ООО &laquo;МАРКОМНЕТ&raquo;',
		                'markomnet_service'=>'ООО &laquo;МАРКОМНЕТ сервис&raquo;',
		                'ooomcn'=>'ООО &laquo;МСН&raquo;',
		                'all4net'=>'ООО &laquo;ОЛФОНЕТ&raquo;',
		                'ooocmc'=>'ООО &laquo;Си Эм Си&raquo;',
		                'mcm'=>'ООО &laquo;МСМ&raquo;',
		                'all4geo'=>'ООО &laquo;Олфогео&raquo;',
		                'wellstart'=>'ООО &laquo;Веллстарт&raquo;'
		);
		
		if ($action=='add'){
			$f=array(
				'user'			=> get_param_protected('user'),
				'usergroup'		=> get_param_protected('usergroup'),
				'depart_id'		=> get_param_protected('depart_id'),
				'name'			=> get_param_protected('name'),
				'pass_text'		=> password_gen(8),
				'firms'	    	=> get_param_protected('user2firm'),
			);
			$f['pass']=password::hash($f['pass_text']);
			$id=$f['user'];
			if (!$id) {
				trigger_error('Оператор должен иметь имя');
			} else if (isset($d_users[$id])) {
				trigger_error('Такой оператор уже существует');
			} else {
				$db->Query('insert into user_users (user,usergroup,name,pass,depart_id) values ("' . $id . '","' . $f['usergroup'] . '","' . $f['name'] . '","'.$f['pass'].'","'.$f["depart_id"].'")');
				trigger_error('Оператор '.$id.' создан. Пароль: '.$f['pass_text']);
				$this->d_users_get($d_users);
				$design->assign_by_ref("users",$d_users);

                //Доступ по фирмам
                $f['firms'] = (count($f['firms']) > 0) ? implode(',', array_keys($f['firms'])) : '';
                if ($f['firms'] != implode(',', array_keys($Firms)))
                    $db->Query('insert into user_grant_users (name,resource,access) values ("'.$id.'","firms","'.$f['firms'].'")');
			}
		} else if ($action=='edit'){
			$f=array(
				'user'				=> get_param_protected('newuser'),
				'usergroup'			=> get_param_protected('usergroup'),
				'depart_id'			=> get_param_protected('depart_id'),
				'name'				=> get_param_protected('name'),
				'rights'			=> get_param_raw('rights',array()),
				'rights_radio'		=> get_param_raw('rights_radio',array()),
				'pass1'				=> password::hash(get_param_raw('pass1')),
				'pass2'				=> password::hash(get_param_raw('pass2')),
				'trouble_redirect'	=> get_param_protected('trouble_redirect'),
				'pass'				=> (get_param_raw('pass1').get_param_raw('pass2')),
				'email'				=> get_param_protected('email'),
				'phone_work'		=> get_param_protected('phone_work'),
				'phone_mobile'		=> get_param_protected('phone_mobile'),
				'icq'				=> get_param_protected('icq'),
                'enabled'           => get_param_protected('enabled', 'no'),
			    'firms'	          	=> get_param_protected('user2firm'),
				);

            if(!$f["enabled"]) $f["enabled"] = "no";

			if (!$f['user']) {
				trigger_error('Оператор должен иметь имя');
			} else if (($f['user']!=$id) && isset($d_users[$f['user']])){
				trigger_error('Такой оператор уже существует');
			} else {
				$add='';
				if ($f['pass']) {
					if ($f['pass1']==$f['pass2']){
						$add='pass="'.$f['pass1'].'",';
						trigger_error('Пароль изменён');
					} else trigger_error('Пароли не совпадают');
				}
				global $module_usercontrol;
				if (!isset($module_usercontrol)){
					trigger_error('Модуль usercontrol не установлен - фотография меняться не будет');
					$q_photo='';
				} else $q_photo=$module_usercontrol->process_photo($id);

				$db->Query('update user_users set '.$add.
								'user="'.$f['user'].'",' .
								'usergroup="'.$f['usergroup'].'",'.
								'depart_id="'.$f['depart_id'].'",'.
								'name="'.$f['name'].'",'.
								'email="'.$f['email'].'",'.
								'phone_work="'.$f['phone_work']. '",' .
								'phone_mobile="'.$f['phone_mobile']. '",' .
								'icq="'.$f['icq']. '",' .
								'enabled="'.$f['enabled']. '",' .
								'trouble_redirect="'.$f['trouble_redirect'].'"'.$q_photo .' where user="'.$id.'"');

				if (access('users','grant')){
					$R=array();
					$db->Query('select * from user_grant_users where (name="'. $id. '")');
					while ($r=$db->NextRecord()) {
						$R[$r['resource']]=$r['access'];
					}

                    //Доступ по фирмам
                    $f['firms'] = (is_array($f['firms']) && count($f['firms']) > 0) ? implode(',', array_keys($f['firms'])) : '';
                    if ($f['firms'] == implode(',', array_keys($Firms))) {
                        //Если выбраны все и была запись в таблице - удалим запись из таблицы
                        if (isset($R['firms'])) 
                            $db->Query('delete from user_grant_users where (name="'.$id.'") and (resource="firms")');
                    } else {
                        if (!isset($R['firms'])) {
                            $db->Query('insert into user_grant_users (name,resource,access) values ("'.$id.'","firms","'.$f['firms'].'")');
                        } else {
                            $db->Query('update user_grant_users set access="'.$f['firms'].'" where (name="'.$id.'") and (resource="firms")');
                        }
                    }

					$this->d_rights_get($d_rights);
					foreach ($d_rights as $i=>$v){
						if (isset($f['rights_radio'][$i]) && $f['rights_radio'][$i]){
							$f['rights'][$i]=$this->rights_validate($f['rights'][$i],$v['values']);
							if (isset($R[$i])){
								if ($R[$i]!=$f['rights'][$i]){
									$db->Query('update user_grant_users set access="'.$f['rights'][$i].'" where (name="'.$id.'") and (resource="'.$i.'")');
								}
							} else $db->Query('insert into user_grant_users (name,resource,access) values ("'.$id.'","'.$i.'","'.$f['rights'][$i].'")');
						} else {
							if (isset($R[$i])) $db->Query('delete from user_grant_users where (name="'.$id.'") and (resource="'.$i.'")');
						}
					}
				}
				trigger_error('Оператор '.$id.' изменён');
				$this->d_users_get($d_users);
				$design->assign_by_ref("users",$d_users);
			}
		}

		if (!$id) return;
		if (!isset($d_users[$id])) return;

		$d_groups[$d_users[$id]['usergroup']]['selected']=" selected";
		$design->assign_by_ref("user",$d_users[$id]);
		$design->assign_by_ref("usergroup",$d_users[$id]['usergroup']);

		if (!isset($d_rights)) $this->d_rights_get($d_rights);
		$R=array();
		$db->Query('select * from user_grant_groups where (name="'. $d_users[$id]['usergroup']. '")');
		while ($r=$db->NextRecord()) {
			$R[$r['resource']]=$r['access'];
		}
		$design->assign("rights_group",$R);

		$R2=array();
		$db->Query('select * from user_grant_users where (name="'. $id. '")');
		while ($r=$db->NextRecord()) {
			$R[$r['resource']]=$r['access'];
			$R2[$r['resource']]=$r['access'];
		}

		if (!isset($R['firms'])) {
		    foreach ($Firms as $k=>$v) $user2firm[$k] = 1;
		} else {
		    $tmp = explode(',', $R['firms']);
		    foreach ($tmp as $k) $user2firm[$k] = 1;
		}
		$design->assign("user2firm",$user2firm);

		$design->assign_by_ref("rights_real",$R);
		$design->assign("rights_user",$R2);

        $design->assign("firms",$Firms);

		$design->AddMain('users/main_user.tpl');
	}

	//работа со списком групп
	function users_groups($action){
		global $design,$db,$user;
		$this->d_groups_get($d_groups);
		$id=get_param_protected('id');

		if ($action=='delete'){
			if (!isset($d_groups[$id])) {
				trigger_error('Такого оператора не существует');
			} else {
				$db->Query('delete from user_groups where usergroup="'.$id.'"');
				trigger_error('Группа '.$id.' удалёна');
				$this->d_users_get($d_groups);
				$design->assign_by_ref("users",$d_users);
			}
		}

		$design->AddMain('users/main_groups.tpl');
	}

	//работа с выбранной группой
	function users_group($action) {
		global $design,$db,$user;
		$this->d_groups_get($d_groups);
		$id=get_param_protected('id');
		if ($action=='add'){
			$f=array(
				'usergroup'			=> get_param_protected('usergroup'),
				'comment'			=> get_param_protected('comment'),
				);
			$id=$f['usergroup'];
			if (!$id) {
				trigger_error('Группа должна иметь имя');
			} else if (isset($d_groups[$id])) {
				trigger_error('Такая группа уже существует');
			} else {
				$db->Query('insert into user_groups (usergroup,comment) values ("' . $id . '","' . $f['comment'] . '")');
				trigger_error('Группа '.$id.' создана');
				$this->d_groups_get($d_groups);
			}
		} else if ($action=='edit'){
			$f=array(
				'usergroup'			=> get_param_protected('newusergroup'),
				'comment'			=> get_param_protected('comment'),
				'rights'			=> get_param_raw('rights',array()),
				);
			if (!$f['usergroup']) {
				trigger_error('Группа должна иметь имя');
			} else if (($f['usergroup']!=$id) && isset($d_users[$f['usergroup']])){
				trigger_error('Такая группа уже существует');
			} else {
				$db->Query('update user_groups set usergroup="'.$f['usergroup'].'",comment="'.$f['comment'].'" where usergroup="'.$id.'"');
				if (access('users','grant')){
					$R=array();
					$db->Query('select * from user_grant_groups where (name="'. $id. '")');
					while ($r=$db->NextRecord()) {
						$R[$r['resource']]=$r['access'];
					}
					$this->d_rights_get($d_rights);
					foreach ($d_rights as $i=>$v){
						if (isset($f['rights'][$i]) && $f['rights'][$i]){
							$f['rights'][$i]=$this->rights_validate($f['rights'][$i],$v['values']);
							if (isset($R[$i])){
								if ($R[$i]!=$f['rights'][$i]){
									$db->Query('update user_grant_groups set access="'.$f['rights'][$i].'" where (name="'.$id.'") and (resource="'.$i.'")');
								}
							} else $db->Query('insert into user_grant_groups (name,resource,access) values ("'.$id.'","'.$i.'","'.$f['rights'][$i].'")');
						} else if (isset($R[$i])) $db->Query('delete from user_grant_groups where (name="'.$id.'") and (resource="'.$i.'")');
					}
				}
				trigger_error('Группа '.$id.' изменена');
				$this->d_users_get($d_users);
				$design->assign_by_ref("users",$d_users);
			}
		}

		if (!$id) return;
		if (!isset($d_groups[$id])) return;

		$design->assign_by_ref("usergroup",$d_groups[$id]);

		if (!isset($d_rights)) $this->d_rights_get($d_rights);

		$R=array();
		$db->Query('select * from user_grant_groups where (name="'. $id. '")');
		while ($r=$db->NextRecord()) {
			$R[$r['resource']]=$r['access'];
		}
		$design->assign("rights_group",$R);

		$design->AddMain('users/main_group.tpl');
	}


	//работа со списком отделов
	function users_departs($action){
		global $design,$db,$user;


        if($action == "add")
        {
            $name = get_param_protected("name", "");
            if($name)
            {
                $isSet = false;
                unset($d_departs);
                $this->d_departs_get($d_departs);
                foreach($d_departs as $d)
                {
                    if($d["name"] == $name) {
                        $isSet = true;
                        break;
                    }
                }
                unset($d_departs);
                if($isSet)
                {
                    trigger_error('Отдел '.$name.' уже существует!');
                }else{
                    $db->Query("insert into user_departs set name = '".$name."'");
                }
            }else{
				trigger_error('Отдел не задан!');
            }

        }

        if ($action=='delete'){
            $id=get_param_protected('id');
            if ($id) {
                $db->Query('update user_users set depart_id = 0 where depart_id = "'.$id.'"');
                $db->Query('delete from user_departs where id="'.$id.'"');
                trigger_error('Отдел  удалён');
            }
        }
		$this->d_departs_get($d_departs, false);


		$design->AddMain('users/main_departs.tpl');
	}

	function d_users_get(&$d_users,$group=''){
		global $db,$design;

        if(!is_array($group)) {
            if($group)
            $group = array($group);
        }

        if(!is_array($d_users))
            $d_users = array();

        if($group && in_array("manager", $group))
            $group[] = "account_managers";

		//$d_users=array();
		$db->Query('select u.*, d.name as depart_name from user_users u left join user_departs d on (d.id = u.depart_id)'.($group?' where usergroup in ("'.implode("\",\"",$group).'")':'').' and enabled = "yes" order by u.name');
		while ($r=$db->NextRecord()) $d_users[$r['user']]=$r;


	}
	function d_departs_get(&$d_groups, $widthZero = true){
		global $db,$design;
		$d_groups=array();
        if($widthZero)
		$d_groups=array("0" => array("name" => ""));
		$db->Query('select * from user_departs order by name');
		while ($r=$db->NextRecord()) $d_groups[$r['id']]=$r;
		$design->assign_by_ref("departs",$d_groups);
	}
	function d_groups_get(&$d_groups){
		global $db,$design;
		$d_groups=array();
		$db->Query('select * from user_groups order by usergroup');
		while ($r=$db->NextRecord()) $d_groups[$r['usergroup']]=$r;
		$design->assign_by_ref("groups",$d_groups);
	}
	function d_rights_get(&$d_rights){
		global $db,$design;
		$d_rights=array();
		$d_rights2=array();
		$db->Query('select * from user_rights order by resource');
		while ($r=$db->NextRecord()) {
			preg_match("/^([^_]+)(_.+)?/",$r['resource'],$m); $m=$m[1];
			if (!isset($d_rights2[$m])) $d_rights2[$m]=array();
			$r['values']=explode(',',$r['values']);
			$r['values_desc']=explode(',',$r['values_desc']);
			$d_rights[$r['resource']]=$r;
			$d_rights2[$m][$r['resource']]=$r;
		}
		$design->assign_by_ref("rights",$d_rights2);
	}
	function rights_validate($r,$list){
		$R=explode(',',$r);
		$G=array();
		foreach ($R as $r) {
			if (in_array($r,$list)) $G[]=$r;
		}
		return implode(',',$G);
	}

}

?>
